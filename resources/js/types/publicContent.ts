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
    type: 'intro' | 'audience' | 'process' | 'faq' | 'packages';
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
    hero_tagline?: string | null;
    seo_description?: string;
    blocks: HomeBlock[];
}

export interface SiteInfo {
    name: string;
    positioning: string;
    category: string;
    address: {
        street: string;
        locality: string;
        region: string;
        country: string;
        summary: string;
    };
    emails: string[];
    phones: { tel: string; display: string; whatsapp: string | null }[];
    social: { label: string; url: string }[];
}

export interface ImageSource {
    src: string;
    srcset?: string | null;
    width: number | null;
    height: number | null;
}

export interface HeroSlide extends ImageSource {
    id: number;
    alt: string;
    position: string;
    mobile: ImageSource | null;
    mobile_position: string;
}

export interface HeroSliderSettings {
    autoplay: boolean;
    interval_seconds: number;
}

export interface PackageSummary {
    slug: string;
    name: string;
    summary: string;
    audience: string;
    inclusions: string[];
    includes: { slug: string; name: string } | null;
    selectable: boolean;
}

export interface TeamProfile {
    id: number;
    name: string;
    role: string | null;
    bio: string | null;
    portrait: ImageSource | null;
}

export interface FaqItem {
    id?: number;
    question: string;
    answer: string;
}

export interface FaqGroup {
    name: string;
    items: FaqItem[];
}

export interface MenuLink {
    label: string;
    href: string;
    external: boolean;
}

export interface SiteNavigation {
    header: MenuLink[];
    footer: MenuLink[];
}

export interface ArticleCover extends ImageSource {
    alt: string;
}

export interface ArticleSummary {
    slug: string;
    title: string;
    excerpt: string;
    categories: string[];
    published_at: string | null;
    reading_minutes: number;
    cover: ArticleCover | null;
}

export interface ArticleDetail extends ArticleSummary {
    body: string;
    seo_description: string;
    author_name: string | null;
    reviewer_name: string | null;
    reviewed_at: string | null;
    tags: string[];
    sources: { title: string; url: string; accessed_on: string | null }[];
    related_service_ids: number[];
    related_post_ids: number[];
}

export interface InsightCategory {
    slug: string;
    name: string;
    description?: string | null;
    seo_description?: string | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedArticles {
    data: ArticleSummary[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

export interface TopicHubContent {
    slug: string;
    title: string;
    intro: string;
    seo_description: string;
    featured: ArticleSummary | null;
    articles: ArticleSummary[];
}

export interface ServiceOption {
    id: number;
    title: string;
}

export interface LeadSubmittedFlash {
    reference: string | null;
}
