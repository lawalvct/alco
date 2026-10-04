export interface ServiceSummary {
    slug: string;
    title: string;
    summary: string;
}

export interface ServiceDetail extends ServiceSummary {
    body: string;
    audience: string;
    seo_description: string;
    deliverables: { label: string }[];
}

export interface HomeBlock {
    type: 'intro' | 'audience' | 'process' | 'faq';
    data: {
        eyebrow: string;
        title: string;
        body?: string;
        items?: {
            label?: string;
            detail?: string;
            question?: string;
            answer?: string;
        }[];
        steps?: { title: string; detail: string }[];
    };
}

export interface HomeContent {
    title: string;
    hero_eyebrow: string;
    hero_line_one: string;
    hero_line_two: string;
    hero_accent: string;
    hero_description: string;
    seo_description?: string;
    blocks: HomeBlock[];
}
