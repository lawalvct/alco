<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculators', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title', 160);
            $table->string('summary', 350);
            $table->text('intro')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('calculator_config_versions', function (Blueprint $table) {
            $table->id();
            $table->string('tool_slug', 80);
            $table->string('scope_key', 120)->default('standard');
            $table->unsignedInteger('version');
            $table->string('label', 160);
            $table->decimal('rate_percent', 9, 5);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('reviewer_name', 120)->nullable();
            $table->date('reviewed_at')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['tool_slug', 'scope_key', 'version']);
            $table->index(['tool_slug', 'scope_key', 'status', 'effective_from'], 'calculator_config_lookup');
        });

        $now = now();
        DB::table('calculators')->insert(array_map(
            static fn (array $item, int $position): array => [
                'slug' => $item[0],
                'title' => $item[1],
                'summary' => $item[2],
                'sort_order' => $position + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                ['vat', 'VAT calculator', 'Explore inclusive and exclusive VAT arithmetic using an entered rate or an approved rule.'],
                ['withholding-tax', 'Withholding tax calculator', 'Estimate an amount withheld from an eligible payment base.'],
                ['gross-net', 'Gross to net / net to gross', 'Explore a simple percentage and fixed deduction in either direction.'],
                ['markup-margin', 'Markup vs margin', 'Compare profit measured against cost and selling price.'],
                ['break-even', 'Break-even calculator', 'Estimate the units and sales needed to cover fixed costs.'],
                ['loan-interest', 'Loan and interest calculator', 'Model simple interest, compound growth or an amortizing loan.'],
                ['depreciation', 'Depreciation calculator', 'Create a book depreciation schedule using straight line or reducing balance.'],
                ['working-capital-current-ratio', 'Working capital and current ratio', 'Compare current assets and current liabilities.'],
                ['receivables-dso', 'Receivables collection / DSO', 'Estimate collection days from credit sales and receivables.'],
                ['inventory-turnover', 'Inventory turnover', 'Explore turnover and inventory days for a matching period.'],
                ['profitability-roi', 'Profitability and ROI', 'See gross profit, operating profit, margins and simple investment return.'],
            ],
            array_keys(range(1, 11)),
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_config_versions');
        Schema::dropIfExists('calculators');
    }
};
