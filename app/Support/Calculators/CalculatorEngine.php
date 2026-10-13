<?php

namespace App\Support\Calculators;

use App\Models\CalculatorConfigVersion;
use Illuminate\Validation\ValidationException;

/** Server-authoritative formulas. Rates entered in the UI are percentages. */
final class CalculatorEngine
{
    private const SCALE = 10;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function calculate(string $slug, array $input, string $currency): array
    {
        $metrics = match ($slug) {
            'vat' => $this->vat($input),
            'withholding-tax' => $this->withholding($input),
            'gross-net' => $this->grossNet($input),
            'markup-margin' => $this->markupMargin($input),
            'break-even' => $this->breakEven($input),
            'loan-interest' => $this->loan($input),
            'depreciation' => $this->depreciation($input),
            'working-capital-current-ratio' => $this->workingCapital($input),
            'receivables-dso' => $this->receivables($input),
            'inventory-turnover' => $this->inventory($input),
            'profitability-roi' => $this->profitability($input),
            default => abort(404),
        };

        return [
            ...$metrics,
            'currency' => $currency,
            'formula_version' => '1.0',
            'rounding' => 'Money is displayed to 2 decimal places, half up. Intermediate arithmetic uses 10 decimal places. Loan interest and payments round each period.',
        ];
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function vat(array $v): array
    {
        [$rate, $rule] = $this->taxRate('vat', $v);
        $amount = self::v($v, 'amount');
        $fraction = self::div($rate, '100');
        $base = $v['mode'] === 'inclusive' ? self::div($amount, self::add('1', $fraction)) : $amount;
        $tax = $v['mode'] === 'inclusive' ? self::sub($amount, $base) : self::mul($base, $fraction);
        $total = $v['mode'] === 'inclusive' ? $amount : self::add($base, $tax);

        return [
            'metrics' => [self::m('Base amount', $base), self::m('VAT estimate', $tax), self::m('Total amount', $total), self::m('Rate used', $rate, 'percent')],
            'assumptions' => [
                $rule ? "Approved rule: {$rule->label} (version {$rule->version}, effective {$rule->effective_from->toDateString()})." : 'Manual illustrative rate; no Nigerian statutory treatment has been selected.',
                'Exempt, zero-rated and out-of-scope transactions require a separate classification and are not inferred here.',
            ],
            'config_version' => $rule?->id,
            'source_url' => $rule?->source_url,
        ];
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function withholding(array $v): array
    {
        [$rate, $rule] = $this->taxRate('withholding-tax', $v);
        $base = self::v($v, 'eligible_base');
        $payable = self::v($v, 'payable_amount');
        self::require(bccomp($base, $payable, self::SCALE) <= 0, 'eligible_base', 'The eligible base cannot exceed the agreed payable amount.');
        $withheld = self::mul($base, self::div($rate, '100'));
        $cash = self::sub($payable, $withheld);

        return [
            'metrics' => [self::m('Eligible base', $base), self::m('Amount withheld', $withheld), self::m('Cash payable', $cash), self::m('Rate used', $rate, 'percent')],
            'assumptions' => [
                $rule ? "Approved rule: {$rule->label} (version {$rule->version}, effective {$rule->effective_from->toDateString()})." : 'Manual illustrative rate; no transaction or recipient classification has been verified.',
                'Enter only the amount eligible for withholding. VAT treatment and exclusions must be determined separately.',
            ],
            'config_version' => $rule?->id,
            'source_url' => $rule?->source_url,
        ];
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array{numeric-string, CalculatorConfigVersion|null}
     */
    private function taxRate(string $slug, array $v): array
    {
        if ($v['rate_source'] === 'approved') {
            $rule = CalculatorConfigVersion::applicable($slug, $v['scope_key'] ?? 'standard', $v['transaction_date']);
            self::require($rule !== null, 'rate_source', 'No approved rule covers this classification and transaction date. Use a clearly labeled manual estimate or ask ALCO to configure a reviewed rule.');

            return [self::v(['rate' => $rule->rate_percent], 'rate'), $rule];
        }

        $rate = self::optionalValue($v, 'rate_percent');
        if ($rate === '') {
            throw ValidationException::withMessages(['rate_percent' => 'Enter an illustrative rate from 0 to 100%.']);
        }
        self::require(bccomp($rate, '100', self::SCALE) <= 0, 'rate_percent', 'Enter an illustrative rate from 0 to 100%.');

        return [$rate, null];
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function grossNet(array $v): array
    {
        $rate = self::v($v, 'deduction_percent');
        self::require(bccomp($rate, '100', self::SCALE) < 0, 'deduction_percent', 'The deduction rate must be below 100%.');
        $factor = self::sub('1', self::div($rate, '100'));
        $fixed = self::v($v, 'fixed_deduction');
        $gross = $v['mode'] === 'net_to_gross' ? self::div(self::add(self::v($v, 'amount'), $fixed), $factor) : self::v($v, 'amount');
        $percentDeduction = self::mul($gross, self::div($rate, '100'));
        $net = self::sub(self::sub($gross, $percentDeduction), $fixed);
        self::require(bccomp($net, '0', self::SCALE) >= 0, 'fixed_deduction', 'Deductions exceed the gross amount.');

        return self::result([self::m('Gross', $gross), self::m('Percentage deduction', $percentDeduction), self::m('Fixed deduction', $fixed), self::m('Net', $net)], 'A flat deduction model only; it does not calculate PAYE, reliefs or statutory payroll contributions.');
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function markupMargin(array $v): array
    {
        $cost = self::v($v, 'cost');
        $target = self::v($v, 'target_percent');
        $price = match ($v['mode']) {
            'target_markup' => self::mul($cost, self::add('1', self::div($target, '100'))),
            'target_margin' => self::targetMarginPrice($cost, $target),
            default => self::optionalValue($v, 'selling_price'),
        };
        if ($price === '') {
            throw ValidationException::withMessages(['selling_price' => 'Enter the selling price.']);
        }
        $profit = self::sub($price, $cost);

        return self::result([
            self::m('Selling price', $price), self::m('Profit', $profit),
            self::ratio('Markup on cost', $profit, $cost, true),
            self::ratio('Margin on price', $profit, $price, true),
        ], 'Markup compares profit with cost; margin compares the same profit with selling price.');
    }

    /**
     * @param  numeric-string  $cost
     * @param  numeric-string  $target
     * @return numeric-string
     */
    private static function targetMarginPrice(string $cost, string $target): string
    {
        self::require(bccomp($target, '100', self::SCALE) < 0, 'target_percent', 'Target margin must be below 100%.');

        return self::div($cost, self::sub('1', self::div($target, '100')));
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function breakEven(array $v): array
    {
        $fixed = self::v($v, 'fixed_costs');
        $price = self::v($v, 'unit_price');
        $contribution = self::sub($price, self::v($v, 'variable_cost'));
        $metrics = [self::m('Contribution per unit', $contribution)];

        if (bccomp($contribution, '0', self::SCALE) <= 0) {
            $metrics[] = self::undefined('Break-even units', 'No finite break-even: the contribution per unit is zero or negative.');

            return self::result($metrics, 'This model assumes a constant selling price and variable cost per unit.');
        }

        $units = self::ceil(self::div($fixed, $contribution));
        $targetUnits = self::ceil(self::div(self::add($fixed, self::v($v, 'target_profit')), $contribution));
        $metrics[] = self::m('Break-even units', $units, 'units');
        $metrics[] = self::m('Sales at break-even', self::mul($units, $price));
        $metrics[] = self::m('Units for target profit', $targetUnits, 'units');

        return self::result($metrics, 'Whole units are rounded up. This model assumes a constant selling price and variable cost per unit.');
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function loan(array $v): array
    {
        $principal = self::v($v, 'principal');
        $annualRate = self::v($v, 'annual_rate_percent');
        $years = (int) $v['years'];
        $frequency = (int) $v['frequency'];
        $periods = $years * $frequency;
        self::require($years >= 1 && $years <= 50, 'years', 'Use a whole number of years between 1 and 50.');

        if ($v['mode'] === 'simple') {
            $interest = self::mul($principal, self::mul(self::div($annualRate, '100'), (string) $years));

            return self::result([self::m('Interest', $interest), self::m('Total due', self::add($principal, $interest))], 'Simple interest uses the annual rate and whole years.');
        }

        $periodicRate = self::div(self::div($annualRate, '100'), (string) $frequency);

        if ($v['mode'] === 'compound') {
            $balance = self::mul($principal, bcpow(self::add('1', $periodicRate), (string) $periods, self::SCALE));

            return self::result([self::m('Future balance', $balance), self::m('Interest earned', self::sub($balance, $principal))], 'Compounding occurs at the selected frequency; contributions and fees are excluded.');
        }

        $factor = (float) $periodicRate;
        $scheduled = $factor == 0.0
            ? self::money(self::div($principal, (string) $periods))
            : self::money((string) ((float) $principal * $factor / (1 - (1 + $factor) ** (-$periods))));
        $balance = self::money($principal);
        $totalInterest = '0.00';
        $totalPaid = '0.00';
        $schedule = [];

        for ($period = 1; $period <= $periods && bccomp($balance, '0', 2) > 0; $period++) {
            $interest = self::money(self::mul($balance, $periodicRate));
            $principalPaid = self::sub($scheduled, $interest);
            self::require(bccomp($principalPaid, '0', 2) > 0, 'annual_rate_percent', 'This rate and term do not reduce the loan balance at cent precision.');
            if ($period === $periods || bccomp($principalPaid, $balance, 2) >= 0) {
                $principalPaid = $balance;
            }
            $payment = self::money(self::add($principalPaid, $interest));
            $balance = self::money(self::sub($balance, $principalPaid));
            $totalInterest = self::money(self::add($totalInterest, $interest));
            $totalPaid = self::money(self::add($totalPaid, $payment));
            $schedule[] = ['period' => $period, 'payment' => $payment, 'interest' => $interest, 'principal' => self::money($principalPaid), 'balance' => $balance];
        }

        return self::result([self::m('Scheduled payment', $scheduled), self::m('Total interest', $totalInterest), self::m('Total paid', $totalPaid)], 'Periodic rate is the annual nominal rate divided by the selected frequency. The final payment reconciles the remaining balance.', $schedule);
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function depreciation(array $v): array
    {
        $cost = self::v($v, 'cost');
        $residual = self::v($v, 'residual');
        self::require(bccomp($residual, $cost, 2) <= 0, 'residual', 'Residual value cannot exceed cost.');
        $years = (int) $v['years'];
        self::require($years >= 1 && $years <= 100, 'years', 'Use a whole number of years between 1 and 100.');
        $rate = self::v($v, 'reducing_rate_percent');
        if ($v['mode'] === 'reducing') {
            self::require(bccomp($rate, '0', self::SCALE) > 0 && bccomp($rate, '100', self::SCALE) <= 0, 'reducing_rate_percent', 'Enter a reducing balance rate above 0% and no more than 100%.');
        }
        $opening = self::money($cost);
        $schedule = [];
        $straight = self::div(self::sub($cost, $residual), (string) $years);

        for ($year = 1; $year <= $years; $year++) {
            $remaining = self::sub($opening, $residual);
            $expense = $v['mode'] === 'straight_line'
                ? ($year === $years ? $remaining : self::money($straight))
                : self::money(self::mul($opening, self::div($rate, '100')));
            if (bccomp($expense, $remaining, 2) > 0) {
                $expense = $remaining;
            }
            $expense = self::money($expense);
            $closing = self::money(self::sub($opening, $expense));
            $schedule[] = ['period' => $year, 'opening' => $opening, 'expense' => $expense, 'balance' => $closing];
            $opening = $closing;
        }

        return self::result([self::m('First-year expense', $schedule[0]['expense']), self::m('Closing book value', $opening)], 'Book depreciation only. Reducing balance may remain above residual at the end of the selected life.', $schedule);
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function workingCapital(array $v): array
    {
        $assets = self::v($v, 'current_assets');
        $liabilities = self::v($v, 'current_liabilities');
        $inventory = self::v($v, 'inventory');
        self::require(bccomp($inventory, $assets, 2) <= 0, 'inventory', 'Inventory cannot exceed current assets in this simplified model.');
        $metrics = [self::m('Working capital', self::sub($assets, $liabilities)), self::ratio('Current ratio', $assets, $liabilities)];
        if (bccomp($inventory, '0', 2) > 0) {
            $metrics[] = self::ratio('Quick ratio (simplified)', self::sub($assets, $inventory), $liabilities);
        }

        return self::result($metrics, 'Ratios do not imply a universal health threshold. Quick ratio subtracts inventory only.');
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function receivables(array $v): array
    {
        $average = self::div(self::add(self::v($v, 'opening'), self::v($v, 'closing')), '2');
        $sales = self::v($v, 'credit_sales');

        return self::result([
            self::m('Average receivables', $average),
            bccomp($sales, '0', 2) === 0 ? self::undefined('Days sales outstanding', 'Undefined when credit sales are zero.') : self::m('Days sales outstanding', self::mul(self::div($average, $sales), self::v($v, 'period_days')), 'days'),
        ], 'Opening and closing trade receivables and credit sales must cover the same period.');
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function inventory(array $v): array
    {
        $average = self::div(self::add(self::v($v, 'opening'), self::v($v, 'closing')), '2');
        $cogs = self::v($v, 'cogs');
        $turnover = bccomp($average, '0', 2) > 0 ? self::div($cogs, $average) : null;

        return self::result([
            self::m('Average inventory', $average),
            $turnover === null ? self::undefined('Inventory turnover', 'Undefined when average inventory is zero.') : self::m('Inventory turnover', $turnover, 'times'),
            $turnover === null || bccomp($turnover, '0', self::SCALE) === 0 ? self::undefined('Inventory days', 'Undefined when turnover is zero.') : self::m('Inventory days', self::div(self::v($v, 'period_days'), $turnover), 'days'),
        ], 'COGS and opening/closing inventory must use a consistent cost basis and period.');
    }

    /**
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function profitability(array $v): array
    {
        $revenue = self::v($v, 'revenue');
        $gross = self::sub($revenue, self::v($v, 'cogs'));
        $operating = self::sub($gross, self::v($v, 'operating_costs'));
        $gain = self::optionalValue($v, 'net_gain');
        $investment = self::optionalValue($v, 'investment_cost');
        self::require(($gain === '') === ($investment === ''), 'investment_cost', 'Enter both investment cost and net gain to calculate ROI.');

        return self::result([
            self::m('Gross profit', $gross), self::m('Operating profit', $operating),
            self::ratio('Gross margin', $gross, $revenue, true), self::ratio('Operating margin', $operating, $revenue, true),
            $investment === '' ? self::undefined('Simple ROI', 'Enter investment cost and net gain to calculate ROI.') : self::ratio('Simple ROI', self::v($v, 'net_gain'), $investment, true),
        ], 'ROI uses the entered net gain and is not annualized.');
    }

    /**
     * @param  array<string, mixed>  $values
     * @return numeric-string
     */
    private static function v(array $values, string $key, string $default = '0'): string
    {
        $value = isset($values[$key]) && $values[$key] !== '' ? (string) $values[$key] : $default;
        if (! is_numeric($value)) {
            throw ValidationException::withMessages([$key => 'Enter a valid number.']);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return numeric-string|''
     */
    private static function optionalValue(array $values, string $key): string
    {
        return ! isset($values[$key]) || $values[$key] === '' ? '' : self::v($values, $key);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    private static function add(string $a, string $b): string
    {
        return bcadd($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    private static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    private static function mul(string $a, string $b): string
    {
        return bcmul($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    private static function div(string $a, string $b): string
    {
        return bcdiv($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $value
     * @return numeric-string
     */
    private static function money(string $value): string
    {
        $negative = str_starts_with($value, '-');
        $rounded = bcadd(ltrim($value, '-'), '0.005', 2);

        return $negative && bccomp($rounded, '0', 2) !== 0 ? bcsub('0', $rounded, 2) : $rounded;
    }

    /**
     * @param  numeric-string  $value
     * @return numeric-string
     */
    private static function ceil(string $value): string
    {
        $whole = bcadd($value, '0', 0);

        return bccomp($value, $whole, self::SCALE) > 0 ? bcadd($whole, '1', 0) : $whole;
    }

    /**
     * @param  numeric-string  $value
     * @return array{label: string, value: string|null, unit: string, note: string|null}
     */
    private static function m(string $label, string $value, string $unit = 'currency'): array
    {
        return ['label' => $label, 'value' => match ($unit) {
            'currency' => self::money($value),
            'units' => bcadd($value, '0', 0),
            default => number_format((float) $value, 2, '.', ''),
        }, 'unit' => $unit, 'note' => null];
    }

    /** @return array{label: string, value: null, unit: string, note: string} */
    private static function undefined(string $label, string $note): array
    {
        return ['label' => $label, 'value' => null, 'unit' => 'text', 'note' => $note];
    }

    /**
     * @param  numeric-string  $numerator
     * @param  numeric-string  $denominator
     * @return array<string, mixed>
     */
    private static function ratio(string $label, string $numerator, string $denominator, bool $percent = false): array
    {
        if (bccomp($denominator, '0', self::SCALE) === 0) {
            return self::undefined($label, 'Undefined because the denominator is zero.');
        }

        return self::m($label, self::mul(self::div($numerator, $denominator), $percent ? '100' : '1'), $percent ? 'percent' : 'times');
    }

    /**
     * @param  list<array<string, mixed>>  $metrics
     * @param  list<array<string, mixed>>  $schedule
     * @return array<string, mixed>
     */
    private static function result(array $metrics, string $assumption, array $schedule = []): array
    {
        return ['metrics' => $metrics, 'assumptions' => [$assumption], 'schedule' => $schedule, 'config_version' => null, 'source_url' => null];
    }

    private static function require(bool $valid, string $field, string $message): void
    {
        if (! $valid) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
