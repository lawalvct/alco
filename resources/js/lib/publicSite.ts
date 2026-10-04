export const navigation = [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Services', href: '/services' },
    { label: 'Insights', href: '/insights' },
    { label: 'Tools', href: '/tools' },
    { label: 'Contact', href: '/contact' },
] as const;

export const services = [
    {
        slug: 'bookkeeping',
        title: 'Bookkeeping',
        description:
            'Keep everyday records organised and ready for clearer decisions.',
    },
    {
        slug: 'financial-reporting',
        title: 'Financial Reporting',
        description:
            'Understand performance through timely, useful financial reports.',
    },
    {
        slug: 'tax-filings',
        title: 'Tax Filings',
        description:
            'Prepare filing information and navigate tax obligations with support.',
    },
    {
        slug: 'business-solutions',
        title: 'Business Solutions',
        description:
            'Bring financial clarity to planning and everyday business choices.',
    },
] as const;

export const plannedTools = [
    'VAT',
    'Withholding tax',
    'Gross-to-net / net-to-gross',
    'Markup vs margin',
    'Break-even',
    'Loan / interest',
    'Depreciation',
    'Working capital / current ratio',
    'Receivables / DSO',
    'Inventory turnover',
    'Profitability / ROI',
] as const;
