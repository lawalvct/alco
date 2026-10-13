<?php

namespace Tests\Feature;

use App\Models\Calculator;
use App\Models\CalculatorConfigVersion;
use App\Models\User;
use App\Support\Calculators\CalculatorGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_tools_have_public_pages_and_a_compute_endpoint(): void
    {
        $this->get('/tools')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Public/ToolsIndex')->has('tools', 11)->etc());

        foreach (['vat', 'withholding-tax', 'gross-net', 'markup-margin', 'break-even', 'loan-interest', 'depreciation', 'working-capital-current-ratio', 'receivables-dso', 'inventory-turnover', 'profitability-roi'] as $slug) {
            $this->get("/tools/{$slug}")->assertOk();
        }
    }

    public function test_calculator_pages_have_distinct_search_content_and_guest_access(): void
    {
        config(['app.url' => 'https://alcosolutions.ng']);

        foreach (['vat', 'withholding-tax', 'gross-net', 'markup-margin', 'break-even', 'loan-interest', 'depreciation', 'working-capital-current-ratio', 'receivables-dso', 'inventory-turnover', 'profitability-roi'] as $slug) {
            $this->get("/tools/{$slug}")->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Public/Calculator')
                ->where('tool.slug', $slug)
                ->where('tool.seoDescription', CalculatorGuide::for($slug)['description'])
                ->has('guide.steps', 3)
                ->has('guide.faqs', 2)
                ->has('relatedTools', 3)
                ->where('canonical', "https://alcosolutions.ng/tools/{$slug}")
                ->etc());
        }
    }

    public function test_calculator_schema_and_cms_search_overrides_match_visible_content(): void
    {
        config(['app.url' => 'https://alcosolutions.ng']);
        Calculator::query()->where('slug', 'vat')->update([
            'seo_title' => 'VAT estimate for invoices',
            'seo_description' => 'Compare tax-inclusive and tax-exclusive invoice figures.',
            'guide_intro' => 'A revised explanation for invoice figures.',
            'guide_steps' => [['step' => 'Enter the invoice amount.']],
            'guide_faqs' => [['question' => 'Can I change the rate?', 'answer' => 'Yes, enter an illustrative rate.']],
        ]);

        $html = $this->get('/tools/vat?utm_source=search')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('tool.seoTitle', 'VAT estimate for invoices')
                ->where('tool.seoDescription', 'Compare tax-inclusive and tax-exclusive invoice figures.')
                ->where('guide.intro', 'A revised explanation for invoice figures.')
                ->where('guide.steps', ['Enter the invoice amount.'])
                ->where('guide.faqs.0.question', 'Can I change the rate?')
                ->where('canonical', 'https://alcosolutions.ng/tools/vat')
                ->etc())->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);
        $graph = json_decode($match[1], true)['@graph'];
        $application = collect($graph)->firstWhere('@type', 'WebApplication');
        $this->assertSame('https://alcosolutions.ng/tools/vat', $application['url']);
        $this->assertSame('Compare tax-inclusive and tax-exclusive invoice figures.', $application['description']);
        $this->assertTrue($application['isAccessibleForFree']);
        $this->assertSame('0', $application['offers']['price']);
    }

    public function test_hidden_calculator_is_not_public_or_listed_in_the_sitemap(): void
    {
        Calculator::query()->where('slug', 'vat')->update(['is_visible' => false]);

        $this->get('/tools/vat')->assertNotFound();
        $this->postJson('/tools/vat/calculate', [])->assertNotFound();
        $this->get('/tools')->assertInertia(fn (Assert $page) => $page->has('tools', 10)->etc());
        $this->get('/sitemap.xml')->assertDontSee('/tools/vat', false);
    }

    public function test_plan_calculation_fixtures(): void
    {
        $cases = [
            ['vat', ['amount' => '1000', 'mode' => 'exclusive', 'rate_source' => 'manual', 'rate_percent' => '10', 'transaction_date' => '2026-10-13'], ['VAT estimate' => '100.00', 'Total amount' => '1100.00']],
            ['vat', ['amount' => '1100', 'mode' => 'inclusive', 'rate_source' => 'manual', 'rate_percent' => '10', 'transaction_date' => '2026-10-13'], ['Base amount' => '1000.00', 'VAT estimate' => '100.00']],
            ['withholding-tax', ['eligible_base' => '1000', 'payable_amount' => '1000', 'rate_source' => 'manual', 'rate_percent' => '5', 'transaction_date' => '2026-10-13'], ['Amount withheld' => '50.00', 'Cash payable' => '950.00']],
            ['gross-net', ['mode' => 'gross_to_net', 'amount' => '1000', 'deduction_percent' => '10', 'fixed_deduction' => '50'], ['Net' => '850.00']],
            ['gross-net', ['mode' => 'net_to_gross', 'amount' => '850', 'deduction_percent' => '10', 'fixed_deduction' => '50'], ['Gross' => '1000.00']],
            ['markup-margin', ['mode' => 'price', 'cost' => '100', 'selling_price' => '125'], ['Markup on cost' => '25.00', 'Margin on price' => '20.00']],
            ['markup-margin', ['mode' => 'target_margin', 'cost' => '100', 'target_percent' => '20'], ['Selling price' => '125.00']],
            ['break-even', ['fixed_costs' => '1000', 'unit_price' => '50', 'variable_cost' => '30'], ['Break-even units' => '50', 'Sales at break-even' => '2500.00']],
            ['loan-interest', ['mode' => 'amortizing', 'principal' => '1200', 'annual_rate_percent' => '0', 'years' => '1', 'frequency' => '12'], ['Scheduled payment' => '100.00', 'Total interest' => '0.00']],
            ['depreciation', ['mode' => 'straight_line', 'cost' => '1000', 'residual' => '100', 'years' => '3'], ['First-year expense' => '300.00', 'Closing book value' => '100.00']],
            ['working-capital-current-ratio', ['current_assets' => '2000', 'current_liabilities' => '1000'], ['Working capital' => '1000.00', 'Current ratio' => '2.00']],
            ['receivables-dso', ['opening' => '1000', 'closing' => '1000', 'credit_sales' => '10000', 'period_days' => '100'], ['Days sales outstanding' => '10.00']],
            ['inventory-turnover', ['opening' => '1000', 'closing' => '1000', 'cogs' => '5000', 'period_days' => '100'], ['Inventory turnover' => '5.00', 'Inventory days' => '20.00']],
            ['profitability-roi', ['revenue' => '1000', 'cogs' => '600', 'operating_costs' => '200', 'net_gain' => '200', 'investment_cost' => '1000'], ['Gross margin' => '40.00', 'Operating margin' => '20.00', 'Simple ROI' => '20.00']],
        ];

        foreach ($cases as [$slug, $input, $expected]) {
            $result = $this->postJson("/tools/{$slug}/calculate", $input)->assertOk()->json();
            $actual = collect($result['metrics'])->pluck('value', 'label');
            foreach ($expected as $label => $value) {
                $this->assertSame($value, $actual[$label], "{$slug}: {$label}");
            }
        }
    }

    public function test_tax_rule_is_not_guessed_and_invalid_values_are_rejected(): void
    {
        $this->postJson('/tools/vat/calculate', [
            'amount' => '1000', 'mode' => 'exclusive', 'rate_source' => 'approved',
            'transaction_date' => '2026-10-13', 'scope_key' => 'standard',
        ])->assertUnprocessable()->assertJsonValidationErrors('rate_source');

        $this->postJson('/tools/vat/calculate', [
            'amount' => '1000', 'mode' => 'exclusive', 'rate_source' => 'manual',
            'rate_percent' => '150', 'transaction_date' => '2026-10-13',
        ])->assertUnprocessable()->assertJsonValidationErrors('rate_percent');
    }

    public function test_approved_tax_rule_is_dated_immutable_and_rejects_overlap(): void
    {
        $reviewer = User::factory()->create();
        $rule = CalculatorConfigVersion::create([
            'tool_slug' => 'vat', 'scope_key' => 'sample', 'version' => 1,
            'label' => 'Hypothetical fixture only', 'rate_percent' => '10',
            'effective_from' => '2026-01-01', 'effective_to' => '2026-12-31',
            'reviewer_name' => 'Fixture reviewer', 'reviewed_at' => '2026-01-01',
            'source_url' => 'https://example.com/fixture',
        ]);
        $rule->approve($reviewer);

        $this->postJson('/tools/vat/calculate', [
            'amount' => '1000', 'mode' => 'exclusive', 'rate_source' => 'approved',
            'transaction_date' => '2026-06-01', 'scope_key' => 'sample',
        ])->assertOk()->assertJsonPath('metrics.1.value', '100.00')->assertJsonPath('config_version', $rule->id);

        $this->postJson('/tools/vat/calculate', [
            'amount' => '1000', 'mode' => 'exclusive', 'rate_source' => 'approved',
            'transaction_date' => '2027-01-01', 'scope_key' => 'sample',
        ])->assertUnprocessable()->assertJsonValidationErrors('rate_source');

        $overlap = CalculatorConfigVersion::create([
            'tool_slug' => 'vat', 'scope_key' => 'sample', 'version' => 2,
            'label' => 'Overlapping fixture', 'rate_percent' => '5',
            'effective_from' => '2026-06-01', 'reviewer_name' => 'Fixture reviewer',
            'reviewed_at' => '2026-06-01', 'source_url' => 'https://example.com/fixture',
        ]);
        $this->expectException(ValidationException::class);
        $overlap->approve($reviewer);
    }
}
