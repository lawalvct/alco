<?php

namespace App\Support\Calculators;

final class CalculatorCatalog
{
    /** @return array<string, array{description: string, formula: string, fields: list<array<string, mixed>>}> */
    public static function all(): array
    {
        $amount = fn (string $name, string $label, bool $required = true): array => self::number($name, $label, $required, 'currency');
        $rate = fn (string $name, string $label, bool $required = true): array => self::number($name, $label, $required, 'percent');
        $days = fn (string $name, string $label, bool $required = true): array => self::number($name, $label, $required, 'days');

        return [
            'vat' => [
                'description' => 'Compare a price before and after VAT. An entered rate is an illustrative estimate until an approved rule is available.',
                'formula' => 'Exclusive: tax = base × rate. Inclusive: base = total ÷ (1 + rate); tax = total − base.',
                'fields' => [
                    $amount('amount', 'Amount'),
                    self::choice('mode', 'Amount type', ['exclusive' => 'VAT exclusive', 'inclusive' => 'VAT inclusive']),
                    self::choice('rate_source', 'Rate source', ['manual' => 'Manual estimate', 'approved' => 'Approved CMS rule']),
                    $rate('rate_percent', 'Entered VAT rate (%)', false),
                    self::date('transaction_date', 'Transaction date'),
                    self::text('scope_key', 'Approved rule key', false, 'standard'),
                ],
            ],
            'withholding-tax' => [
                'description' => 'Estimate a withholding amount from the eligible base you provide. Confirm the base and rule separately.',
                'formula' => 'Withheld = eligible base × rate; cash payable = agreed payable amount − withheld.',
                'fields' => [
                    $amount('eligible_base', 'Eligible payment base'),
                    $amount('payable_amount', 'Agreed payable amount'),
                    self::choice('rate_source', 'Rate source', ['manual' => 'Manual estimate', 'approved' => 'Approved CMS rule']),
                    $rate('rate_percent', 'Entered WHT rate (%)', false),
                    self::date('transaction_date', 'Transaction date'),
                    self::text('scope_key', 'Transaction / recipient rule key', false, 'standard'),
                ],
            ],
            'gross-net' => [
                'description' => 'Model a simple percentage and fixed deduction. This is not a statutory payroll calculation.',
                'formula' => 'Net = gross × (1 − rate) − fixed deduction; gross = (target net + fixed deduction) ÷ (1 − rate).',
                'fields' => [
                    self::choice('mode', 'Direction', ['gross_to_net' => 'Gross to net', 'net_to_gross' => 'Net to gross']),
                    $amount('amount', 'Gross amount or target net'),
                    $rate('deduction_percent', 'Percentage deduction (%)'),
                    $amount('fixed_deduction', 'Fixed deduction', false),
                ],
            ],
            'markup-margin' => [
                'description' => 'See why the same profit produces different markup and margin percentages.',
                'formula' => 'Profit = price − cost; markup = profit ÷ cost; margin = profit ÷ price.',
                'fields' => [
                    self::choice('mode', 'Known value', ['price' => 'Selling price', 'target_markup' => 'Target markup', 'target_margin' => 'Target margin']),
                    $amount('cost', 'Cost'),
                    $amount('selling_price', 'Selling price', false),
                    $rate('target_percent', 'Target percentage (%)', false),
                ],
            ],
            'break-even' => [
                'description' => 'Estimate whole units and sales needed to cover fixed costs at the stated price and unit cost.',
                'formula' => 'Contribution = unit price − variable cost; units = (fixed costs + target profit) ÷ contribution, rounded up.',
                'fields' => [
                    $amount('fixed_costs', 'Fixed costs'),
                    $amount('unit_price', 'Selling price per unit'),
                    $amount('variable_cost', 'Variable cost per unit'),
                    $amount('target_profit', 'Target profit', false),
                ],
            ],
            'loan-interest' => [
                'description' => 'Compare simple interest, compound growth and an amortizing loan. Fees and APR are excluded.',
                'formula' => 'Simple: P × r × t. Compound: P × (1 + r/k)^(k×t). Loan: periodic payment from the periodic rate and number of payments.',
                'fields' => [
                    self::choice('mode', 'Method', ['amortizing' => 'Amortizing loan', 'simple' => 'Simple interest', 'compound' => 'Compound growth']),
                    $amount('principal', 'Principal'),
                    $rate('annual_rate_percent', 'Annual rate (%)'),
                    self::number('years', 'Term (years)', true, 'years'),
                    self::choice('frequency', 'Payments / compounding', ['12' => 'Monthly (12)', '4' => 'Quarterly (4)', '1' => 'Annual (1)']),
                ],
            ],
            'depreciation' => [
                'description' => 'Prepare a book depreciation schedule, not a tax capital allowance computation.',
                'formula' => 'Straight line = (cost − residual) ÷ life. Reducing balance = opening book value × rate, capped at residual.',
                'fields' => [
                    self::choice('mode', 'Method', ['straight_line' => 'Straight line', 'reducing' => 'Reducing balance']),
                    $amount('cost', 'Asset cost'),
                    $amount('residual', 'Residual value'),
                    self::number('years', 'Useful life (years)', true, 'years'),
                    $rate('reducing_rate_percent', 'Annual reducing rate (%)', false),
                ],
            ],
            'working-capital-current-ratio' => [
                'description' => 'Compare current assets and liabilities, with an optional simplified quick ratio.',
                'formula' => 'Working capital = assets − liabilities; current ratio = assets ÷ liabilities.',
                'fields' => [
                    $amount('current_assets', 'Current assets'),
                    $amount('current_liabilities', 'Current liabilities'),
                    $amount('inventory', 'Inventory (optional)', false),
                ],
            ],
            'receivables-dso' => [
                'description' => 'Estimate days sales outstanding using trade receivables and credit sales for the same period.',
                'formula' => 'Average receivables = (opening + closing) ÷ 2; DSO = average receivables ÷ credit sales × days.',
                'fields' => [
                    $amount('opening', 'Opening trade receivables'),
                    $amount('closing', 'Closing trade receivables'),
                    $amount('credit_sales', 'Credit sales for the period'),
                    $days('period_days', 'Days in the period'),
                ],
            ],
            'inventory-turnover' => [
                'description' => 'Compare cost of goods sold with average inventory for a matching period.',
                'formula' => 'Average inventory = (opening + closing) ÷ 2; turnover = COGS ÷ average inventory; days = period days ÷ turnover.',
                'fields' => [
                    $amount('opening', 'Opening inventory'),
                    $amount('closing', 'Closing inventory'),
                    $amount('cogs', 'Cost of goods sold'),
                    $days('period_days', 'Days in the period'),
                ],
            ],
            'profitability-roi' => [
                'description' => 'See gross and operating margins plus a simple return on an explicitly entered net gain.',
                'formula' => 'Gross profit = revenue − COGS; operating profit = gross profit − operating costs; ROI = net investment gain ÷ investment cost.',
                'fields' => [
                    $amount('revenue', 'Revenue'),
                    $amount('cogs', 'Cost of goods sold'),
                    $amount('operating_costs', 'Operating costs'),
                    $amount('net_gain', 'Net investment gain', false),
                    $amount('investment_cost', 'Investment cost', false),
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function number(string $name, string $label, bool $required, string $unit): array
    {
        return compact('name', 'label', 'required', 'unit') + ['type' => 'number', 'default' => null];
    }

    /**
     * @param  array<int|string, string>  $options
     * @return array<string, mixed>
     */
    private static function choice(string $name, string $label, array $options): array
    {
        return compact('name', 'label', 'options') + ['type' => 'select', 'required' => true, 'default' => array_key_first($options)];
    }

    /** @return array<string, mixed> */
    private static function date(string $name, string $label): array
    {
        return compact('name', 'label') + ['type' => 'date', 'required' => true, 'default' => null];
    }

    /** @return array<string, mixed> */
    private static function text(string $name, string $label, bool $required, string $default): array
    {
        return compact('name', 'label', 'required', 'default') + ['type' => 'text'];
    }
}
