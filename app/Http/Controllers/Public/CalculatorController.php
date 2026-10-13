<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
use App\Models\CalculatorConfigVersion;
use App\Models\Post;
use App\Support\Calculators\CalculatorCatalog;
use App\Support\Calculators\CalculatorEngine;
use App\Support\Calculators\CalculatorGuide;
use App\Support\StructuredData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CalculatorController extends Controller
{
    public function index(StructuredData $structuredData): Response
    {
        $tools = Calculator::query()->where('is_visible', true)->orderBy('sort_order')->get()
            ->map(fn (Calculator $tool): array => $this->summary($tool))->all();

        $structuredData->breadcrumbs([
            ['name' => 'Home', 'path' => '/'],
            ['name' => 'Tools', 'path' => '/tools'],
        ]);

        return Inertia::render('Public/ToolsIndex', [
            'tools' => $tools,
        ]);
    }

    public function show(string $slug, StructuredData $structuredData): Response
    {
        $tool = $this->visibleTool($slug);
        $definition = CalculatorCatalog::all()[$slug];
        $guide = CalculatorGuide::for($slug);
        $heading = str_contains(strtolower($tool->title), 'calculator') ? $tool->title : "{$tool->title} calculator";
        $guide['intro'] = trim((string) $tool->guide_intro) ?: $guide['intro'];
        $guide['example'] = trim((string) $tool->guide_example) ?: $guide['example'];
        $steps = collect($tool->guide_steps ?? [])->pluck('step')->filter()->values()->all();
        $guide['steps'] = $steps ?: $guide['steps'];
        $guide['faqs'] = $tool->guide_faqs ?: $guide['faqs'];
        $seoTitle = trim((string) $tool->seo_title) ?: $guide['title'];
        $seoDescription = trim((string) $tool->seo_description) ?: $guide['description'];
        $related = Calculator::query()->where('is_visible', true)
            ->whereIn('slug', CalculatorGuide::related($slug))->get()->keyBy('slug');
        $relatedTools = collect(CalculatorGuide::related($slug))
            ->map(fn (string $relatedSlug): ?Calculator => $related->get($relatedSlug))
            ->filter()
            ->map(fn (Calculator $relatedTool): array => $this->summary($relatedTool))
            ->values()->all();

        $structuredData->breadcrumbs([
            ['name' => 'Home', 'path' => '/'],
            ['name' => 'Tools', 'path' => '/tools'],
            ['name' => $heading, 'path' => "/tools/{$slug}"],
        ]);
        $structuredData->add([
            '@type' => 'WebApplication',
            '@id' => StructuredData::absolute("/tools/{$slug}#calculator"),
            'name' => $heading,
            'description' => $seoDescription,
            'url' => StructuredData::absolute("/tools/{$slug}"),
            'applicationCategory' => 'FinanceApplication',
            'operatingSystem' => 'Web browser',
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => $tool->currency],
            'provider' => ['@id' => StructuredData::absolute('/#organization')],
        ]);

        $relatedPosts = $slug === 'vat'
            ? Post::published()->inCategory('tax-compliance')->with(['categories', 'coverMediaAsset'])->limit(3)->get()->map(fn (Post $post): array => $post->publicSummary())->all()
            : [];

        return Inertia::render('Public/Calculator', [
            'tool' => $this->summary($tool) + [
                'heading' => $heading,
                'intro' => $tool->intro ?: $definition['description'],
                'currency' => $tool->currency,
                'seoTitle' => $seoTitle,
                'seoDescription' => $seoDescription,
            ],
            'definition' => $definition,
            'guide' => $guide,
            'relatedTools' => $relatedTools,
            'relatedPosts' => $relatedPosts,
            'today' => now()->toDateString(),
            'approvedRules' => in_array($slug, ['vat', 'withholding-tax'], true)
                ? CalculatorConfigVersion::query()->where('tool_slug', $slug)->where('status', 'approved')->orderBy('scope_key')->get(['scope_key', 'label', 'effective_from', 'effective_to', 'version'])
                    ->map(fn (CalculatorConfigVersion $rule): array => [
                        'key' => $rule->scope_key,
                        'label' => $rule->label,
                        'period' => $rule->effective_from->toDateString().' to '.($rule->effective_to?->toDateString() ?? 'open ended'),
                        'version' => $rule->version,
                    ])->all()
                : [],
        ]);
    }

    public function calculate(Request $request, string $slug, CalculatorEngine $engine): JsonResponse
    {
        $tool = $this->visibleTool($slug);
        $definition = CalculatorCatalog::all()[$slug];
        $rules = [];

        foreach ($definition['fields'] as $field) {
            $name = $field['name'];
            $required = $field['required'] ? 'required' : 'nullable';
            $rules[$name] = match ($field['type']) {
                'number' => [
                    $required,
                    'numeric',
                    'min:0',
                    'max:'.(in_array($name, ['years', 'period_days'], true) ? '100' : ($field['unit'] === 'percent' ? '100' : '1000000000000')),
                    'regex:'.(in_array($name, ['years', 'period_days'], true) ? '/^\\d+$/' : ($field['unit'] === 'percent' ? '/^\\d+(?:\\.\\d{1,5})?$/' : '/^\\d+(?:\\.\\d{1,2})?$/')),
                ],
                'date' => [$required, 'date_format:Y-m-d'],
                'select' => [$required, Rule::in(array_keys($field['options']))],
                default => [$required, 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            };
        }

        $input = $request->validate($rules);

        return response()->json($engine->calculate($slug, $input, $tool->currency));
    }

    private function visibleTool(string $slug): Calculator
    {
        abort_unless(array_key_exists($slug, CalculatorCatalog::all()), 404);

        return Calculator::query()->where('slug', $slug)->where('is_visible', true)->firstOrFail();
    }

    /** @return array{slug: string, title: string, summary: string} */
    private function summary(Calculator $tool): array
    {
        return ['slug' => $tool->slug, 'title' => $tool->title, 'summary' => $tool->summary];
    }
}
