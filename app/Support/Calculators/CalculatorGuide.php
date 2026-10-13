<?php

namespace App\Support\Calculators;

/**
 * Starting copy for public calculator landing pages. CMS overrides
 * let ALCO refine each explanation without changing the calculation engine.
 */
final class CalculatorGuide
{
    /**
     * @return array{title: string, description: string, intro: string, steps: list<string>, example: string, faqs: list<array{question: string, answer: string}>}
     */
    public static function for(string $slug): array
    {
        return self::all()[$slug] ?? abort(404);
    }

    /** @return list<string> */
    public static function related(string $slug): array
    {
        return [
            'vat' => ['withholding-tax', 'gross-net', 'profitability-roi'],
            'withholding-tax' => ['vat', 'gross-net', 'working-capital-current-ratio'],
            'gross-net' => ['profitability-roi', 'markup-margin', 'working-capital-current-ratio'],
            'markup-margin' => ['break-even', 'profitability-roi', 'inventory-turnover'],
            'break-even' => ['markup-margin', 'profitability-roi', 'loan-interest'],
            'loan-interest' => ['break-even', 'working-capital-current-ratio', 'profitability-roi'],
            'depreciation' => ['profitability-roi', 'loan-interest', 'working-capital-current-ratio'],
            'working-capital-current-ratio' => ['receivables-dso', 'inventory-turnover', 'profitability-roi'],
            'receivables-dso' => ['working-capital-current-ratio', 'inventory-turnover', 'profitability-roi'],
            'inventory-turnover' => ['receivables-dso', 'working-capital-current-ratio', 'profitability-roi'],
            'profitability-roi' => ['break-even', 'markup-margin', 'working-capital-current-ratio'],
        ][$slug] ?? [];
    }

    /**
     * @return array<string, array{title: string, description: string, intro: string, steps: list<string>, example: string, faqs: list<array{question: string, answer: string}>}>
     */
    private static function all(): array
    {
        return [
            'vat' => [
                'title' => 'VAT calculator: inclusive or exclusive',
                'description' => 'Use ALCO’s free VAT calculator to compare VAT-inclusive and VAT-exclusive amounts in Nigeria. Enter your own rate or select an approved, dated rule.',
                'intro' => 'Work backwards from a VAT-inclusive total or add VAT to a price before tax. The calculator shows the base amount, estimated VAT and total, with the rate and assumptions used.',
                'steps' => [
                    'Enter the transaction amount and choose whether it already includes VAT.',
                    'Choose a manual illustrative rate, or an approved rule for the transaction date and classification when one is available.',
                    'Calculate and review the base, VAT amount and total before using the figures in your records.',
                ],
                'example' => 'For a hypothetical 10% rate and a VAT-exclusive amount of ₦1,000, the arithmetic gives ₦100 VAT and a ₦1,100 total. This example is not a statement of the current statutory rate.',
                'faqs' => [
                    ['question' => 'What is the difference between VAT-inclusive and VAT-exclusive?', 'answer' => 'An exclusive amount is the price before VAT; an inclusive amount already contains it. Select the matching mode so the calculator adds VAT or extracts it correctly.'],
                    ['question' => 'Does the tool decide whether a transaction is taxable?', 'answer' => 'No. It calculates the arithmetic for the rate you enter or an approved rule you select. Exempt, zero-rated and out-of-scope treatment need a separate review.'],
                ],
            ],
            'withholding-tax' => [
                'title' => 'Withholding tax calculator',
                'description' => 'Estimate withholding tax on an eligible payment base with ALCO’s free calculator. Compare the withheld amount and cash payable using an entered or reviewed rate.',
                'intro' => 'See how a chosen withholding rate affects a payment. Keep the eligible base separate from the agreed payable amount so exclusions and VAT treatment are not silently assumed.',
                'steps' => [
                    'Enter the portion of the payment that you have determined is eligible for withholding.',
                    'Enter the agreed payable amount and choose an illustrative rate or an approved dated rule.',
                    'Review the estimated withholding amount and remaining cash payable.',
                ],
                'example' => 'If an eligible base and payable amount are both ₦1,000, an illustrative 5% rate produces ₦50 withheld and ₦950 cash payable. The rate is an example, not a statutory recommendation.',
                'faqs' => [
                    ['question' => 'Should I enter the whole invoice as the eligible base?', 'answer' => 'Enter only the amount you have determined is eligible. The tool does not classify the transaction, recipient or VAT component for you.'],
                    ['question' => 'Is the manual rate an official Nigerian WHT rate?', 'answer' => 'No. A manual rate is an illustrative input. ALCO can configure sourced, reviewed rules with effective dates in the CMS.'],
                ],
            ],
            'gross-net' => [
                'title' => 'Gross to net and net to gross calculator',
                'description' => 'Calculate gross from a target net amount, or net from gross, using a simple percentage and fixed deduction. Free gross-to-net calculator by ALCO.',
                'intro' => 'Model a flat percentage deduction and a fixed deduction in either direction. This is useful for quick planning when you already know the deduction assumptions.',
                'steps' => [
                    'Choose gross to net or net to gross.',
                    'Enter the known amount, percentage deduction and any fixed deduction.',
                    'Compare the gross amount, each deduction and the resulting net amount.',
                ],
                'example' => 'A gross amount of ₦1,000 with a 10% deduction and a further ₦50 fixed deduction gives ₦850 net.',
                'faqs' => [
                    ['question' => 'Does this calculate Nigerian payroll taxes?', 'answer' => 'No. It is a flat deduction model and does not calculate PAYE, reliefs or statutory payroll contributions.'],
                    ['question' => 'When is net to gross useful?', 'answer' => 'Use it when you know the amount someone needs to receive and want to estimate the gross amount under your stated deductions.'],
                ],
            ],
            'markup-margin' => [
                'title' => 'Markup vs margin calculator',
                'description' => 'Compare markup and gross margin, or find a selling price for a target percentage. ALCO’s free markup vs margin calculator explains the difference.',
                'intro' => 'Markup measures profit against cost, while margin measures the same profit against selling price. This tool calculates both from a price or works backwards from a target.',
                'steps' => [
                    'Enter the cost and choose a known selling price, target markup or target margin.',
                    'Enter the price or target percentage for your chosen method.',
                    'Review selling price, profit, markup on cost and margin on price.',
                ],
                'example' => 'An item costing ₦100 and selling for ₦125 earns ₦25 profit: that is 25% markup on cost but 20% margin on selling price.',
                'faqs' => [
                    ['question' => 'Why are markup and margin different?', 'answer' => 'They use different denominators. Markup divides profit by cost; margin divides profit by selling price.'],
                    ['question' => 'Can a target margin be 100%?', 'answer' => 'Not with a positive cost and finite selling price. The calculator rejects a target margin of 100% or more.'],
                ],
            ],
            'break-even' => [
                'title' => 'Break-even calculator',
                'description' => 'Estimate break-even units, sales at break-even and units for a profit target from fixed costs, selling price and variable cost per unit.',
                'intro' => 'Find the sales volume needed to cover fixed costs at a chosen unit price. The calculator rounds required units up and shows when a non-positive contribution makes break-even impossible.',
                'steps' => [
                    'Enter fixed costs, selling price per unit and variable cost per unit.',
                    'Optionally add a target profit beyond break-even.',
                    'Review contribution per unit, break-even volume and target-profit volume.',
                ],
                'example' => 'With ₦1,000 fixed costs, a ₦50 unit price and ₦30 variable cost, each unit contributes ₦20. Break-even is 50 units and ₦2,500 sales.',
                'faqs' => [
                    ['question' => 'Why are required units rounded up?', 'answer' => 'A fraction of a whole product cannot usually be sold, so the tool rounds the minimum required volume up to the next whole unit.'],
                    ['question' => 'What if variable cost is at least the selling price?', 'answer' => 'The contribution per unit is zero or negative, so no finite sales volume covers fixed costs under these assumptions.'],
                ],
            ],
            'loan-interest' => [
                'title' => 'Loan and interest calculator',
                'description' => 'Compare simple interest, compound growth and an amortizing loan with ALCO’s free loan and interest calculator and payment schedule.',
                'intro' => 'Explore three different ways interest can be modelled. For amortizing loans, the result includes an estimated periodic payment and a schedule that reconciles the final balance.',
                'steps' => [
                    'Choose simple interest, compound growth or an amortizing loan.',
                    'Enter principal, annual nominal rate, whole-year term and frequency.',
                    'Review the balance or payment and inspect the period schedule when applicable.',
                ],
                'example' => 'For a zero-interest ₦1,200 loan repaid in 12 monthly periods over one year, the scheduled payment is ₦100 per month.',
                'faqs' => [
                    ['question' => 'Is the annual rate an APR?', 'answer' => 'No. The amortizing model divides the entered annual nominal rate by the selected payment frequency. Fees and APR calculations are excluded.'],
                    ['question' => 'Why can the last payment differ?', 'answer' => 'Each period is rounded to cents, so the final payment adjusts to clear the remaining balance.'],
                ],
            ],
            'depreciation' => [
                'title' => 'Depreciation calculator',
                'description' => 'Build a straight-line or reducing-balance book depreciation schedule from asset cost, residual value and useful life.',
                'intro' => 'Estimate annual book depreciation and closing book value using a straight-line or reducing-balance method. Review the full schedule before applying the figures to accounts.',
                'steps' => [
                    'Choose straight-line or reducing-balance depreciation.',
                    'Enter asset cost, expected residual value and useful life; add a rate for reducing balance.',
                    'Review first-year expense and the opening, expense and closing values for each year.',
                ],
                'example' => 'A ₦1,000 asset with ₦100 residual value and a three-year life has ₦300 annual straight-line depreciation.',
                'faqs' => [
                    ['question' => 'Is this a tax capital allowance calculator?', 'answer' => 'No. It models book depreciation only; tax allowances and asset-specific rules need a separate review.'],
                    ['question' => 'Does reducing balance always reach residual value by the final year?', 'answer' => 'No. The selected rate may leave the book value above residual after the chosen useful life.'],
                ],
            ],
            'working-capital-current-ratio' => [
                'title' => 'Working capital and current ratio calculator',
                'description' => 'Calculate working capital, current ratio and a simplified quick ratio from current assets, current liabilities and inventory.',
                'intro' => 'Compare short-term assets with short-term liabilities using a currency amount and liquidity ratios. Inventory is optional for the simplified quick ratio.',
                'steps' => [
                    'Enter current assets and current liabilities for the same reporting date.',
                    'Optionally enter the inventory included in current assets.',
                    'Review working capital, current ratio and the simplified quick ratio.',
                ],
                'example' => 'Current assets of ₦2,000 and current liabilities of ₦1,000 give ₦1,000 working capital and a 2.00 current ratio.',
                'faqs' => [
                    ['question' => 'What does a zero current liability amount mean for the ratio?', 'answer' => 'Division by zero is undefined, so the tool reports the ratio as undefined rather than inventing a value.'],
                    ['question' => 'Does one ratio prove a business is healthy?', 'answer' => 'No. Liquidity depends on the timing and quality of assets and liabilities as well as the business context.'],
                ],
            ],
            'receivables-dso' => [
                'title' => 'Days sales outstanding (DSO) calculator',
                'description' => 'Estimate days sales outstanding (DSO) from opening and closing trade receivables, credit sales and days in the period.',
                'intro' => 'Measure collection time by relating average trade receivables to credit sales over a matching period. The result is an estimate based on opening and closing balances.',
                'steps' => [
                    'Enter opening and closing trade receivables for the period.',
                    'Enter credit sales and the number of days in that same period.',
                    'Review average receivables and estimated days sales outstanding.',
                ],
                'example' => 'Average receivables of ₦1,000 and credit sales of ₦10,000 over 100 days give a DSO estimate of 10 days.',
                'faqs' => [
                    ['question' => 'Should cash sales be included?', 'answer' => 'No. The formula uses credit sales because it compares sales with amounts owed by customers.'],
                    ['question' => 'What if credit sales are zero?', 'answer' => 'DSO is undefined because the denominator is zero; the tool displays that limitation explicitly.'],
                ],
            ],
            'inventory-turnover' => [
                'title' => 'Inventory turnover calculator',
                'description' => 'Calculate inventory turnover and inventory days from cost of goods sold, opening and closing inventory, and a matching reporting period.',
                'intro' => 'Compare cost of goods sold with average inventory to estimate how often stock turns. Use the same cost basis and time period for every input.',
                'steps' => [
                    'Enter opening inventory, closing inventory and cost of goods sold.',
                    'Enter the number of days covered by those figures.',
                    'Review average inventory, turnover and estimated inventory days.',
                ],
                'example' => 'Average inventory of ₦1,000 and cost of goods sold of ₦5,000 produce 5.00 turns. Over 100 days, that corresponds to 20 inventory days.',
                'faqs' => [
                    ['question' => 'Why use cost of goods sold rather than revenue?', 'answer' => 'Inventory is normally measured at cost, so using cost of goods sold keeps the numerator and denominator on a consistent basis.'],
                    ['question' => 'What happens when average inventory is zero?', 'answer' => 'Turnover cannot be calculated by dividing by zero, so the result is marked undefined.'],
                ],
            ],
            'profitability-roi' => [
                'title' => 'Profit margin and ROI calculator',
                'description' => 'Calculate gross profit, operating profit, margins and simple ROI from revenue, costs, investment cost and net gain.',
                'intro' => 'See how much revenue remains after cost of goods sold and operating costs. If you enter a separate investment cost and net gain, the tool also computes a simple return on investment.',
                'steps' => [
                    'Enter revenue, cost of goods sold and operating costs for the same period.',
                    'Optionally enter both investment cost and net investment gain for ROI.',
                    'Review gross and operating profit, margins and simple ROI where supplied.',
                ],
                'example' => 'Revenue of ₦1,000, cost of goods sold of ₦600 and operating costs of ₦200 give ₦400 gross profit and ₦200 operating profit.',
                'faqs' => [
                    ['question' => 'Is simple ROI annualized?', 'answer' => 'No. It divides the net gain you enter by investment cost without adjusting for time.'],
                    ['question' => 'Why is ROI separate from operating margin?', 'answer' => 'Operating margin compares operating profit with revenue; ROI compares a stated investment gain with its investment cost.'],
                ],
            ],
        ];
    }
}
