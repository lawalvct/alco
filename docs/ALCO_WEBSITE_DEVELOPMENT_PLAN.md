# ALCO Associate Solutions — Website Development Plan

Version: 1.0 • Prepared: 4 October 2026 • Status: initial Codex development specification

## 1. Project purpose and scope

Build a polished, mobile-first professional services website and a complete Laravel-native CMS for ALCO Associate Solutions. Visitors must be able to understand services, learn through articles, use accounting tools, and request a consultation. Staff must be able to manage content, media, navigation, enquiries, and calculator settings without editing code.

The agreed architecture is one Laravel application: an Inertia/Vue public site with server-rendered first visits and SPA navigation, plus a Filament admin panel. The website is not a bookkeeping ledger, filing portal, or client document vault. Those may become separate future modules.

Launch scope includes all eleven calculator families listed below, the blog/Insights system, service pages, contact and consultation forms, configurable tax parameters, and staff training. AI features are a later phase and must not block launch.

### Business information supplied by the owner

| Field                 | Value                                                                                                     |
| --------------------- | --------------------------------------------------------------------------------------------------------- |
| Business name         | ALCO Associate Solutions                                                                                  |
| CAC business category | Accounting and Auditing Consultancy                                                                       |
| Positioning statement | Professional Accounting Services. Bookkeeping \| Financial Reporting \| Tax Filings \| Business Solutions |
| Address               | 18, Lagos Street, Ebute-Metta, Lagos State, Nigeria                                                       |
| Telephone 1           | +2348140824230                                                                                            |
| Telephone 2           | +2348148484955                                                                                            |
| Leadership            | Abdulsalam Latifat Damilola; Abdulazeez Azeez Olaitan                                                     |
| Facebook handle       | alco_solutions.ng                                                                                         |
| TikTok handle         | alco_solutions.ng                                                                                         |
| LinkedIn handle       | alco_solutions.ng                                                                                         |

Use these exact names and contact details in seed content. Keep social handles as supplied, but confirm actual profile URLs before enabling links; a handle alone does not identify a LinkedIn company URL. Confirm which phone supports WhatsApp before enabling that CTA. Do not invent an email address, credentials, leadership job titles, CAC number, client logos, testimonials, statistics, or audit licensing claims.

### Reference and unresolved inputs

The previous conversation establishes the stack and the expanded blog/tools scope. Foremost Consulting is a reference for useful professional-services functionality, not a theme to clone. Its local project brief at `C:\laragon\www\foremost-consulting\docs\SIMILAR_PROFESSIONAL_SERVICES_PROJECT_BRIEF.md` was not accessible during preparation. The live site could not be retrieved. Do not assume its implementation details were reviewed.

The two images from the original conversation have not been visually assessed for this specification. Before design approval, inspect the original logo/brand files, confirm usage rights, and extract approved colors. Outstanding inputs: domain, logo files, email destination, verified social URLs, service descriptions, leadership bios/photos/titles, brand palette, tax reviewer, privacy wording, hosting access, and SMTP provider. Development can proceed with clearly marked placeholders; public launch requires their resolution.

## 2. Technology and application architecture

| Layer               | Decision                                                                |
| ------------------- | ----------------------------------------------------------------------- |
| Backend             | Laravel 13; PHP 8.3+                                                    |
| Public presentation | Inertia.js, Vue 3, TypeScript                                           |
| Styling             | Tailwind CSS 4; use compatible 4.1+ where required by Filament          |
| Admin               | Filament 5 panel at `/admin`, using its Blade/Livewire architecture     |
| Database            | MySQL 8+ with utf8mb4; confirm supported server release at provisioning |
| Build               | Vite, Composer and npm lockfiles                                        |
| Hosting             | aaPanel-managed VPS, Nginx, PHP-FPM, HTTPS                              |
| SSR                 | Supported Node runtime and supervised Inertia SSR process               |
| Background work     | Database queue initially; supervised worker and Laravel scheduler       |
| Mail                | Authenticated SMTP; queued notifications                                |
| DNS/CDN/spam        | Cloudflare DNS/CDN and server-verified Turnstile                        |
| Authentication      | Laravel session authentication; no public registration at launch        |

Laravel 13 documents PHP 8.3 as its minimum. Filament 5 documents PHP 8.2+, Laravel 11.28+, and Tailwind 4.1+; these stated minimums fit the agreed stack, but the actual dependency graph must pass installation and integration checks. See [Laravel deployment](https://laravel.com/docs/13.x/deployment) and [Filament installation](https://filamentphp.com/docs/5.x/introduction/installation).

Choose a currently supported Inertia release with matching Laravel and Vue adapters at project initialization, verify its SSR runtime requirements, and pin versions in lockfiles. Avoid copying commands from older Inertia releases. Consult [Inertia SSR documentation](https://inertiajs.com/server-side-rendering).

### Responsibilities

- Laravel routes/controllers own routing, publication visibility, validation, policies, and persistence.
- Inertia passes explicitly shaped public data to Vue pages. Never serialize full models containing private fields.
- Vue owns interactive UI, calculator form state, navigation, and progressive enhancement.
- Filament resources use the same models, application services, and authorization rules as the public application.
- Domain services own calculator logic, publication, lead creation, and media processing. Keep controllers and components small.
- Queue jobs handle email, image conversion, scheduled maintenance, and later exports. Scheduled publication must also be enforced by request-time publication queries.

Suggested organization: `app/Models`, `app/Policies`, `app/Services/Calculators`, `app/Services/Publishing`, `app/Filament/Resources`, `app/Http/Requests`, `resources/js/Pages`, `resources/js/Components`, `resources/js/Layouts`, and `tests`. Add repository documentation for setup, content operations, calculator formulas, deployment, and recovery.

Public pages must render meaningful text, headings, links, and metadata on a direct HTTP request. Browser-only APIs must run after mounting. Admin pages remain Filament pages; do not recreate the CMS in Vue. Keep public and admin asset bundles scoped to avoid loading Filament/Livewire on every visitor page.

## 3. Information architecture and sitemap

| Route                                          | Purpose                                        |
| ---------------------------------------------- | ---------------------------------------------- |
| `/`                                            | Homepage                                       |
| `/about`                                       | Business story, approach, leadership           |
| `/team/{slug}`                                 | Optional detailed approved leadership profiles |
| `/services`                                    | Service overview                               |
| `/services/{slug}`                             | Individual service, deliverables, FAQs and CTA |
| `/insights`                                    | Searchable, paginated blog listing             |
| `/insights/category/{slug}`                    | Editorial category archive                     |
| `/insights/topic/vat`                          | Curated VAT content hub                        |
| `/insights/{slug}`                             | Canonical article URL                          |
| `/tools`                                       | Accounting Tools/Calculators directory         |
| `/tools/{slug}`                                | Individual calculator with explanatory content |
| `/resources`                                   | Approved guides, checklists and templates      |
| `/resources/{slug}`                            | Resource information and download              |
| `/faq`                                         | General questions                              |
| `/contact`                                     | Contact details and enquiry form               |
| `/book-consultation`                           | Consultation request form                      |
| `/privacy`, `/terms`, `/calculator-disclaimer` | Approved legal and tool-use information        |
| `/sitemap.xml`, `/robots.txt`                  | Search discovery                               |
| `/admin`                                       | Authenticated CMS                              |

Reserve route segments such as `category` and `topic` so article slugs cannot collide. Use consistent lowercase hyphenated slugs, named routes, redirects for changed published URLs, and real 404 responses. Tool slugs: `vat`, `withholding-tax`, `gross-net`, `markup-margin`, `break-even`, `loan-interest`, `depreciation`, `working-capital-current-ratio`, `receivables-dso`, `inventory-turnover`, `profitability-roi`.

Primary navigation: Home, About, Services, Insights, Tools, Contact, with Book a Consultation as the main CTA. Resources may appear in the footer or Insights menu. Category/search/filter pages need a clear canonical and indexing strategy; do not index every filter combination.

## 4. Design direction and homepage

Create an original, professional accounting theme with readable typography, restrained colors, strong spacing, useful illustrations, and clear calls to action. Use a consistent design token system for colors, type scale, spacing, radii, shadows and buttons. Final colors depend on brand review. Prioritize mobile readability and trust over animation.

Homepage order:

1. Compact contact strip and responsive navigation.
2. Hero with the supplied positioning statement, a short approved benefit statement, Book a Consultation CTA, and Explore Services link.
3. Trust introduction: CAC business category, Lagos location and documented approach; only verified credentials or statistics.
4. Core service cards: bookkeeping, financial reporting, tax filings, business solutions.
5. Who we help: small businesses, growing companies and professionals, subject to owner approval.
6. How engagement works: enquire, discovery, scope/proposal, delivery and ongoing support.
7. Featured Insights, including a VAT guide linking to its content hub and calculator.
8. Featured Accounting Tools with a link to all eleven tools.
9. Leadership introduction with approved bios and photos.
10. Testimonials or case studies only when authentic and consented; omit the section until available.
11. Selected FAQs and consultation CTA.
12. Footer with address, both phone numbers, verified social links, legal links and navigation.

Every section has editable content and visibility/order controls through typed CMS blocks. Use a curated block library rather than unrestricted HTML or arbitrary page scripts. Respect reduced-motion preferences. Provide accessible mobile menus, visible focus, sufficient contrast and useful empty/error states. Use WCAG 2.2 AA as the design and QA target.

## 5. Service content

Proposed launch services, requiring ALCO approval:

- Bookkeeping and accounting support: transaction records, reconciliation, clean reporting inputs.
- Financial reporting: periodic reports, management accounts and interpretation.
- Tax filings and compliance support: VAT, withholding tax and relevant business filings within confirmed service scope.
- Business advisory and solutions: budgeting, cash-flow planning, process improvement and business decision support.
- Payroll support: optional service, subject to confirmation.
- Accounting systems setup and training: optional service, subject to confirmation.
- Audit preparation and accounting review support: describe the verified service accurately; statutory audit claims require confirmation of qualifications and authorization.

Each service page contains title, summary, audience, common problems, deliverables, process, scope exclusions, related FAQs/articles/tools, SEO fields and consultation CTA. Prices or turnaround promises are published only when approved. Do not add unverified tax deadlines or guarantees.

## 6. CMS models, resources and relationships

All editable public content should be manageable in Filament. Prefer normalized relationships for reusable content and validated JSON only for typed blocks/settings. Add timestamps, author/editor attribution, indexes and model policies.

| Model/resource                  | Main fields and relationships                                                                                                            |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| Page                            | Unique slug, template key, title, summary, typed blocks, status, publish time, SEO, revisions                                            |
| Service                         | Slug, title, summary, body/blocks, icon/media, ordering, status; related FAQs, posts, tools                                              |
| Post                            | Slug, title, excerpt, structured body, hero media, author, reviewer, categories, tags, publish/update/review dates, status, SEO, sources |
| Category / Tag                  | Unique slug, name, description, SEO; many posts; archive visibility                                                                      |
| TopicHub                        | Slug, title, intro, featured post and ordered related posts/tools; VAT hub is first seed                                                 |
| AuthorProfile                   | Display name, bio, photo, approved credentials; optional relationship to a CMS user                                                      |
| TeamMember                      | Name, confirmed role, bio, portrait, ordering, public status                                                                             |
| FAQ                             | Question, answer, grouping, ordering; related pages/services/tools                                                                       |
| Resource                        | Slug, title, description, version, accessible file/media, status, category, optional gated download                                      |
| Testimonial / CaseStudy         | Approved quote/content, attribution, consent evidence, media, publication state                                                          |
| MediaAsset                      | Storage path, MIME, dimensions, size, variants, alt text, caption, rights and attribution                                                |
| NavigationMenu / NavigationItem | Menu location, label, internal target or safe external URL, order, parent, visibility                                                    |
| SiteSetting                     | Typed settings groups: business identity, contacts, social URLs, branding, homepage, SEO, form routing                                   |
| Calculator                      | Stable engine key, public slug/title, description, input/output presentation, related content, visibility, SEO                           |
| CalculatorConfigVersion         | Validated settings, jurisdiction, currency, effective interval, sources, reviewer, status and immutable published version                |
| TaxRateRule                     | Config version, tax type, transaction/person classifications, rate/bands, base definition, exemptions, applicability                     |
| Lead                            | Contact, service, source, message, consent record, status, assignee, follow-up date, UTM/referrer summary                                |
| LeadActivity                    | Lead, staff member, note/action/status transition, timestamp; private                                                                    |
| User / Role / Permission        | Staff accounts, roles and policy permissions                                                                                             |
| ContentRevision / AuditEvent    | Entity, actor, timestamp, change summary, revision snapshot; restricted access                                                           |
| Redirect                        | Old path, new path, redirect code; loop/collision validation                                                                             |

Pages/posts use draft, in-review, approved/scheduled, published and archived states. Publication scopes exclude drafts, archived records and future publish dates. Changes to published content should use revisions so drafts do not immediately replace approved content. Use a consistent timezone policy: store instants in UTC, display/edit publication schedules in Africa/Lagos.

Use unique slugs, foreign keys, explicit decimal precision, and indexes on publication dates/status, category relations, lead status/assignee and effective config dates. Sanitize rich text, validate links and restrict embedded components to an allowlist. Secrets such as SMTP passwords and API keys remain environment secrets, never general CMS settings.

Dashboard widgets: enquiries needing action, overdue follow-ups, recent published articles, scheduled content, review-due tax articles/configurations, failed jobs and media conversion issues. Dashboard aggregates must respect each user's permissions.

## 7. Blog / Insights workflow and VAT content hub

Insights is a proper editorial system with article pages, authors, categories, tags, pagination, search, related content, cover images, revisions, previews, scheduled publication and editorial approval. Initial categories: Tax & Compliance, Bookkeeping, Financial Reporting, Business Finance, Payroll, and Accounting Guides. Publish only categories containing useful approved content.

Editorial flow:

1. Author creates draft with audience, outline, body, excerpt, sources and related service/tool links.
2. Editor checks structure, readability, links, image rights, accessibility and SEO.
3. Accounting/tax reviewer checks calculations, current applicability, dates, source interpretation and limits.
4. Publisher approves a revision and publishes now or schedules it.
5. Publication invalidates page/cache/sitemap data and queues any opted-in distribution work.
6. Assign next review date; tax changes trigger updates across related articles and calculator configurations.
7. Preserve URL and revision history; record meaningful corrections and updated dates.

Draft previews use expiring signed links, exclude drafts from search/sitemaps, and send noindex headers. Public article pages include author, reviewer where appropriate, published/updated dates, reading time, table of contents for long content, citations, related posts, relevant tools and consultation CTA. A CMS preview must resemble the final Vue layout.

### VAT as educational content

`/insights/topic/vat` is a content hub, not a substitute for `/tools/vat`. Seed a draft flagship guide, “Understanding VAT in Nigeria: A Practical Guide for Businesses,” plus planned articles on VAT-inclusive/exclusive pricing, invoices, records, filing preparation, and mistakes. Applicable tax rules, exemptions and deadlines require current professional review before publication.

The flagship article may embed a reusable VAT calculator block with explanation and a link to the standalone tool. Article content must remain readable without running the calculator. The standalone calculator links back to the guide and shows its effective settings and limitations. Avoid duplicate full article bodies across the hub and tool page.

Tax articles store jurisdiction, source URLs/titles, source access date, relevant effective dates, reviewed-by and next-review date. The CMS should flag overdue reviews without silently rewriting published content. Comments are out of launch scope; enquiries provide the initial feedback channel.

## 8. Accounting Tools / Calculators

### Shared user experience

Each tool has a useful introduction, labeled inputs, units/currency, examples, validation, Calculate/Reset actions, a results breakdown, formula explanation, assumptions, related articles and contextual service CTA. Tool usage is free and does not require a lead form. Offer accessible print output; downloadable CSV schedules where relevant can follow core calculation completion.

Default display is NGN, with international number formatting and a configurable display currency where meaningful. Format values for display without using formatted strings in arithmetic. Show percentages clearly as percentages; internal rate representation must be documented. Do not promise that currency selection performs exchange conversion.

Calculators must support keyboard use and screen-reader result announcements. Missing or invalid values must produce actionable messages. Display “not defined” with an explanation for zero denominators, not Infinity or NaN. Avoid presenting estimates as filed liabilities, investment recommendations or audit conclusions.

### Formula specification

In the table, rates such as `r`, `m`, and `i` are fractions, not percentage display values. Monetary arithmetic uses decimal precision and explicitly documented rounding. The examples are mathematical QA fixtures, not approved Nigerian tax rates.

| Tool                            | Inputs/modes                                                                                                                                            | Required results and logic                                                                                                                                                                                                                                                                                      |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| VAT                             | Amount, inclusive/exclusive mode, transaction date, approved rule/rate; optional permitted override                                                     | Exclusive: VAT = base × r, gross = base × (1+r). Inclusive: base = gross/(1+r), VAT = gross−base. Show selected tax treatment, rate, base and total. Exempt/out-of-scope treatment must be distinct from zero-rate.                                                                                             |
| Withholding tax                 | Eligible payment base, transaction classification, recipient classification, date, approved applicable rule                                             | Withheld = eligible base × r; cash payable = agreed payable amount−withheld, subject to explicit VAT/base handling. Show the selected rule and what is excluded from the base. Do not apply a universal WHT percentage.                                                                                         |
| Gross-to-net / net-to-gross     | Simple payment mode: gross or target net, percentage deduction and fixed deduction; payroll mode uses approved bands, reliefs and contribution settings | Simple: net = gross × (1−r)−fixed; gross = (net+fixed)/(1−r), r<1. Payroll uses configured progressive rules and bounded numeric solving for target net, with iteration/tolerance limits and non-convergence handling. Display deductions individually. Never label simple flat-rate mode as compliant payroll. |
| Markup vs margin                | Cost plus selling price, or cost plus target markup/margin                                                                                              | Profit = price−cost; markup = profit/cost; margin = profit/price; price for markup = cost × (1+m); price for margin = cost/(1−m), m<1. Treat zero bases explicitly and allow understandable loss results.                                                                                                       |
| Break-even                      | Fixed costs, unit selling price, variable cost per unit, optional target profit                                                                         | Contribution = price−variable cost; break-even units = fixed costs/contribution; required sales = units × price. Round minimum whole units upward. Target-profit units = (fixed+target profit)/contribution. Non-positive contribution has no finite break-even under this model.                               |
| Loan / interest                 | Principal, annual rate, term, frequency; simple interest, compound growth or amortizing loan mode                                                       | Simple interest = P×r×t. Compound balance = P×(1+r/k)^(k×t). Amortizing payment = P×i/[1−(1+i)^−n]; zero-rate payment = P/n. Show periodic rate convention, payment, total interest, total paid and schedule. Fees, flat rates, APR and taxes are excluded unless explicitly implemented.                       |
| Depreciation                    | Cost, residual value, useful life, start/period convention; straight-line or reducing balance                                                           | Straight-line annual expense = (cost−residual)/life. Reducing balance = opening book value × configured/user-entered rate, capped to residual floor. Produce a schedule and closing book value. Label as book depreciation, not tax capital allowances.                                                         |
| Working capital / current ratio | Current assets, current liabilities; optional inventory                                                                                                 | Working capital = assets−liabilities; current ratio = assets/liabilities. Optional quick ratio = (assets−inventory)/liabilities with stated simplification. Avoid universal “healthy” thresholds.                                                                                                               |
| Receivables / DSO               | Opening/closing or average trade receivables, credit sales, days in matching period                                                                     | Average receivables = (opening+closing)/2 when supplied; DSO = average receivables/credit sales × days. Use credit sales, not all revenue by default; zero sales yields undefined DSO.                                                                                                                          |
| Inventory turnover              | Opening/closing or average inventory, cost of goods sold, period days                                                                                   | Average inventory = (opening+closing)/2; turnover = COGS/average inventory; inventory days = days/turnover. Consistent period and cost basis required; zero/negative denominator cases explained.                                                                                                               |
| Profitability / ROI             | Revenue, COGS, operating costs, defined net investment gain and investment cost                                                                         | Gross profit = revenue−COGS; operating profit = gross profit−operating costs; margins = respective profit/revenue. Simple ROI = net investment gain/investment cost × 100%; optionally gain = proceeds−initial cost−additional costs. State period and do not imply annualized return or IRR.                   |

### Configurable settings and approval

Rates and applicable settings are CMS-managed, not literals in Vue components. Settings include VAT/WHT rules, effective dates, payroll bands/reliefs if enabled, default currency, allowed rate overrides, rounding policy, precision, example inputs, loan frequency/rate conventions, default day count, depreciation modes and disclaimers. Mathematical formulas and supported algorithms remain versioned, reviewed code; the CMS cannot execute arbitrary formulas, PHP or JavaScript.

Published configuration versions are immutable. A revision progresses draft → reviewed → approved → effective. Store jurisdiction, effective-from/to, source reference, reviewer and approval time. Reject ambiguous overlapping rules for the same applicability keys and reject invalid bands, impossible percentages and incompatible settings. Select rules by requested transaction date; never silently use today's rate for a historic calculation. If no approved applicable tax rule exists, show an unavailable/unsupported message or clearly labeled manual estimate mode, not a guessed legal rate.

The CMS previews sample calculations before approval. Public tools display configuration version, applicable date, selected rule, source link and last review date. Cache configuration by version and invalidate after approval. Rate changes require independent reviewer/publisher permission and audit history. Do not seed statutory tax rates as production-ready values without ALCO's qualified review.

### Implementation contract

Implement a registry mapping stable engine keys to typed input schemas, validation rules, output contracts and reviewed engines. Store public presentation content separately from calculation logic. Use deterministic server-side PHP calculation services as the authoritative implementation, called through a same-origin rate-limited POST endpoint such as `/tools/{slug}/calculate`; Vue handles form UX. Optional client previews must share fixtures and must not diverge from authoritative results.

Request: engine slug, validated inputs, transaction date when needed, optional approved config version identifier. Server resolves and validates the configuration; reject tampered or unavailable versions. Response: result values as decimal strings, units, breakdown, assumptions, formula version and config version, plus warnings. The browser formats values for display. Compute-only endpoints must not persist submitted financial amounts by default; sanitize logs and exclude request bodies from analytics/error capture.

Use a decimal arithmetic approach suitable for PHP and money; document precision, rounding stage and schedule reconciliation. For loan schedules, reconcile the final payment to zero remaining principal within the documented rounding policy. Do not place monetary inputs in share URLs. Tool endpoints must never accept executable formulas from users or CMS authors.

### Minimum calculator acceptance fixtures

- Hypothetical VAT r=0.10: exclusive 1,000 → tax 100, total 1,100; inclusive 1,100 → base 1,000, tax 100.
- Hypothetical WHT r=0.05, eligible base/payable 1,000 → withheld 50, cash 950.
- Simple gross 1,000, deduction 10%, fixed 50 → net 850; reverse target 850 → gross 1,000.
- Cost 100, selling 125 → markup 25%, margin 20%; target margin 20% → price 125.
- Fixed costs 1,000, price 50, variable cost 30 → 50 break-even units and sales 2,500.
- Loan 1,200 at zero interest over 12 monthly payments → payment 100, interest zero.
- Straight-line cost 1,000, residual 100, life 3 years → annual expense 300.
- Current assets 2,000 and liabilities 1,000 → working capital 1,000 and ratio 2.
- Average receivables 1,000, credit sales 10,000, period 100 days → DSO 10 days.
- COGS 5,000, average inventory 1,000, period 100 days → turnover 5 and inventory days 20.
- Revenue 1,000, COGS 600, operating costs 200 → gross margin 40%, operating margin 20%; net gain 200 on investment 1,000 → ROI 20%.

Also cover zero denominators, invalid negatives, extreme inputs, date boundaries, unavailable/overlapping tax rules, progressive-band boundaries, reverse-calculation convergence and round trips, and decimal rounding. Allow losses/negative working capital where meaningful; reject invalid negative assets/costs in modes where they are unsupported.

## 9. SEO and structured data

Every indexable page has SSR title, description, canonical URL, social metadata and appropriate headings. Use public-only XML sitemaps with accurate modification dates. Exclude admin, previews, drafts, internal search and empty archives. Use unique article and tool copy; avoid automatically publishing thin pages.

Implement Organization or suitable LocalBusiness structured data with approved name/address/phones, verified `sameAs` links and consistent identity. Use Article/BlogPosting on published articles, BreadcrumbList on nested pages, and WebSite information where appropriate. Add FAQ structured data only when visible content and current eligibility rules justify it; do not promise rich results. Do not mark unverified reviews, ratings, credentials or offers. Validate JSON-LD against the visible content and current search documentation during implementation.

Provide image alt text, readable URLs, internal links between services/articles/tools, social preview images, and redirect management. Confirm Search Console/Bing ownership after domain provisioning. Establish a topic plan addressing real business questions; tax articles require reviewed sources and dates. Local search details must match the verified business information.

## 10. Leads and consultation management

Contact and consultation forms collect name, email or phone, service interest, short message and explicit relevant consent. Consultation requests also capture preferred contact method and availability; this is a request, not a confirmed appointment. Do not require confidential financial records. Disable file uploads initially.

Server validates inputs, checks Turnstile, applies rate limits and honeypot defenses, creates the lead transactionally and queues staff notifications. Record notification failures separately; an email failure must not discard a saved lead. Provide a clear success state and duplicate-submit protection. Show genuine contact options when form submission is unavailable.

Lead stages: new, contacted, qualified, consultation requested/scheduled, proposal sent, won, lost and spam/closed. Store assigned staff, next action, follow-up time, internal notes and history. Restrict exports and log them. Retention/deletion rules, privacy notice and marketing consent wording need owner/legal review. Marketing consent must be optional and separate from handling the enquiry.

CMS dashboard highlights unassigned leads and overdue follow-ups. Capture permitted source/UTM information without retaining full sensitive query strings. Newsletter or CRM integrations are future work; no automatic external messaging is implied by this plan.

## 11. Roles and permissions

| Role                    | Intended permissions                                                                      |
| ----------------------- | ----------------------------------------------------------------------------------------- |
| Owner / Super admin     | Staff access, global configuration, all CMS operations; limited trusted accounts          |
| Administrator           | Site operations, approved content and leads; sensitive access assigned explicitly         |
| Editor                  | Edit/review content, media and navigation; publication assigned separately                |
| Author                  | Create/edit own drafts and submit for review                                              |
| Accounting/tax reviewer | Review tax articles and configuration proposals; cannot silently publish own rate changes |
| Publisher               | Publish approved content/configurations and manage scheduling                             |
| Lead manager            | View assigned/permitted leads, update stages and follow-ups                               |

Use Laravel policies for both UI visibility and server-side enforcement. Define permissions such as `posts.create`, `posts.review`, `posts.publish`, `calculator-configs.edit`, `calculator-configs.approve`, `leads.view`, `leads.export`, `users.manage`, and `settings.manage`. Deny by default; test direct endpoint access and cross-user draft/lead access. A vetted permissions package is optional after compatibility checks. Require MFA for privileged staff, disable public registration, and avoid shared accounts.

## 12. Media and content optimization

Use a managed media library with MIME/size/dimension validation, randomized storage names, ownership/rights metadata and accessible alt text. Produce appropriately sized responsive variants, including WebP/AVIF where supported, with fallback originals. Maintain explicit width/height to prevent layout shift, lazy-load below-the-fold images and prioritize the hero image. Strip unnecessary metadata and cap upload size.

Queue expensive conversions and expose processing/error states in CMS. Never serve executable uploads. Sanitize or reject SVGs unless a trusted sanitization workflow is implemented. Keep private media on private storage with authorized signed delivery. Prevent deleting assets used by published content; show usages and allow replacement. Store PDFs/downloads with correct content type and safe disposition; verify accessibility and file rights before publication.

## 13. Security, privacy and operational protection

Use CSRF protection, escaped/sanitized content, parameterized database access, explicit request validation, authorization policies, secure sessions, HTTPS, safe upload handling and rate limits. Verify anti-spam tokens on the server. Apply login throttling, password resets, MFA and least privilege. Establish a CSP compatible with Inertia/Filament and approved third-party scripts; test rather than disabling protections broadly.

Keep `.env`, source control, logs and backups outside web exposure. Set production debug off. Restrict aaPanel/admin access, SSH credentials and database network access. Use separate staging/production credentials and sanitize staging data. Redact PII and calculator inputs from logs. Monitor errors, queue failures and suspicious sign-ins. Maintain dependency updates, vulnerability review, encrypted off-site backups and a tested restore procedure.

Privacy documentation must describe enquiry handling, retention, analytics and external providers, with review against applicable Nigerian data-protection requirements. The plan does not prescribe a legal compliance conclusion. Implement approved consent behavior and data deletion/export procedures; avoid unnecessary financial-data collection.

## 14. Analytics and performance

Choose a privacy-conscious analytics platform or GA4 with approved consent behavior. Track page views, service interest, tool opened/completed, consultation CTA and successful lead submission. Events may contain tool identifier/config version, never financial amounts, email, phone or message. Do not send contact information through URLs or session-replay tools.

Public performance targets: fast mobile loads, no unnecessary admin code, responsive media, route-level code splitting, modest third-party scripts, optimized fonts, paginated listings, eager-loaded relationships and indexed queries. Cache public settings/navigation and published content with reliable invalidation. Do not cache authenticated responses or draft previews at the CDN.

Use Core Web Vitals targets as launch benchmarks: LCP ≤2.5s, INP ≤200ms and CLS ≤0.1, then assess real-user data when available. Laboratory checks are indicative, not a guarantee. SSR must return useful HTML and survive a managed process restart; monitor failures and document graceful behavior. Calculator input forms should remain lightweight.

## 15. QA and release acceptance

Use PHPUnit/Pest for meaningful domain and feature tests, Vitest for isolated frontend behavior where needed, and browser tests for critical workflows. Required coverage:

- Publication states, schedules, preview access, revisions and slug redirects.
- Every calculator fixture, edge case and approved configuration/date selection.
- Role/policy enforcement, unauthorized exports and admin access.
- Contact submission, anti-spam rejection, duplicate submit, queue notification failure and lead persistence.
- SSR content/metadata on direct requests, hydration and SPA navigation/back behavior.
- CMS content/media changes reflected publicly with correct cache invalidation.
- Mobile layouts, keyboard navigation, labels, contrast, focus and screen-reader result updates.
- Broken links, canonical/sitemap exclusions, JSON-LD validation and real HTTP statuses.
- Upload validation, private file delivery, XSS controls and production debug settings.
- Deployment restart, scheduled publishing, queue processing and backup restoration.

Test representative Android/iOS viewport sizes and current Chrome, Firefox, Edge and Safari. No unresolved critical/high security or data-integrity defects at release. ALCO must review public copy, tax content, calculator parameters, contact delivery and CMS workflows. Do not replace professional formula review with software tests.

Launch is accepted when all eleven tools work, staff can safely publish an article and update an approved setting, draft data remains private, a lead reaches the CMS and notification destination, SSR works, backups restore, and the business details/brand/legal pages are approved.

## 16. aaPanel / Nginx deployment and recovery

1. Provision staging first; verify PHP 8.3+ and Laravel-required extensions, plus database/image/decimal extensions selected by dependencies. Confirm Composer, compatible supported Node runtime and MySQL version.
2. Configure Nginx document root to the application's `public` directory, Laravel request routing, PHP-FPM socket, HTTPS and denial of hidden/sensitive files. Never expose the repository root. Laravel's [deployment guide](https://laravel.com/docs/13.x/deployment) documents this requirement.
3. Create least-privileged database user and environment secrets; set production URL, mail settings, queue driver and debug off. Generate/preserve the application key securely.
4. Build from lockfiles in CI or a controlled release directory: install production Composer dependencies, install locked frontend dependencies, build client and SSR bundles, and run tests/checks before promotion.
5. Back up database/media; apply reviewed migrations, link persistent public storage, ensure writable storage/cache ownership and build production caches. Avoid broad writable permissions.
6. Supervise queue and SSR processes using a compatible process manager; bind SSR privately and do not expose its port. Restart/reload after releases. Schedule Laravel's scheduler every minute using the correct PHP binary.
7. Configure Cloudflare origin TLS, caching rules and protected admin behavior. Verify trusted proxy/HTTPS configuration. Exclude forms/admin/previews from shared cache.
8. Smoke-test homepage, article SSR, all tool endpoints, admin, lead delivery, media, queue, scheduler and HTTPS redirects. Check logs and health monitoring.
9. Use atomic release promotion where feasible. Keep a prior code release, compatible assets and persistent media. Database rollback requires a migration-specific plan; switching code alone cannot undo incompatible schema changes.
10. Back up encrypted database/media off-site daily as an initial policy; agree retention, recovery point and recovery time with ALCO. Test restoration before launch and periodically afterward.

Handover includes environment variable inventory without secrets, release steps, process manager configuration, monitoring contacts, backup/restore steps, content manual, rate-change approval procedure and staff onboarding. Set up SMTP delivery monitoring and SPF/DKIM/DMARC according to the approved provider/domain configuration.

## 17. Development phases and deliverables

| Phase                        | Work                                                                                            | Exit criteria                                                               |
| ---------------------------- | ----------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------- |
| 0 — Discovery                | Inspect Foremost brief/assets; confirm brand, services, domain, content owners and tax reviewer | Approved scope, inputs register, wireframes and decision log                |
| 1 — Foundation               | Laravel/Inertia/Vue/TS/Tailwind/Filament setup; auth, policies, migrations, environments and CI | Compatible locked dependencies, admin login and public SSR smoke test       |
| 2 — Design and core CMS      | Theme, layouts, Pages, Services, Team, FAQs, menus/settings and media                           | Responsive approved public pages editable in CMS                            |
| 3 — Insights                 | Posts/taxonomies/authors, revisions, review/scheduling, VAT hub, search and related content     | Draft-to-publication workflow and VAT article/tool linking validated        |
| 4 — Tools platform           | Calculator registry/contracts/config approval; VAT/WHT/gross-net first, then remaining eight    | All eleven engines, edge tests and approved configuration workflow complete |
| 5 — Conversion and discovery | Forms/leads, notifications, resources, SEO/schema, analytics                                    | Enquiry lifecycle, privacy-aware events and public discovery validated      |
| 6 — Hardening and launch     | Content approval, accessibility/performance/security QA, staging deployment, restore drill      | Launch acceptance completed and trained staff can operate CMS               |
| 7 — Post-launch              | Monitor errors/leads/performance, resolve issues, refine content                                | Stable operations and prioritized improvement backlog                       |
| 8 — Future AI                | Optional reviewed content assistance and bounded knowledge assistant                            | Separate approved scope, privacy controls and evaluation gates              |

Phase order may overlap where dependencies permit, but tools and tax content cannot be published before professional review. Estimate effort after brand/content review and the payroll scope decision; do not commit dates based on incomplete inputs.

## 18. Future AI-assisted content features

Start with CMS-only assistance: article outlines, draft summaries, headline/metadata suggestions, FAQ drafts, image alt-text suggestions, content repurposing and broken/outdated content review queues. Staff must review and explicitly approve every publication. AI must never update tax rates, publish content or claim legal accuracy autonomously.

Later, consider an assistant grounded in approved public ALCO content, with source links, clear uncertainty handling and a consultation handoff. Keep calculation engines deterministic; AI can explain an approved result but must not generate the authoritative numeric calculation. Do not send lead messages, client documents or financial inputs to AI providers without a separately approved data-handling design.

AI implementation requires a separate provider/model decision, secure credentials, budgets, logging with redaction, prompt versioning, editorial permissions and evaluations for unsupported tax claims, incorrect citations, prompt injection and privacy leakage. The initial website requires no AI API connection or key.

## 19. Codex implementation instructions

Treat this document as the initial specification. Before coding, inspect applicable repository instructions and existing code, read the referenced Foremost brief if available, confirm dependency compatibility and record decisions. Preserve the agreed stack. Do not scaffold a second public frontend framework or an unrestricted page-builder engine.

Implement one complete vertical slice first: editable service/page, public SSR presentation, an article with review/publication, one configurable calculator, and a lead form. Then extend the established contracts to the remaining resources/tools. Commit migrations, factories/fixtures, policies, meaningful tests and operational documentation with each feature.

Seed verified business details and draft sample content. Mark unapproved copy/configurations clearly. Do not publish invented profiles, endorsements, legal tax settings or credentials. Keep rate/formula review separate from visual design approval. Maintain a task checklist traceable to this specification and a decision log for changes.

Recommended initial backlog: project initialization → publication/policy foundation → public design shell → CMS core resources → blog/VAT hub → calculator registry/config workflow → all eleven engines → leads/notifications → SEO/media/performance → QA/deployment/handover.

## 20. Sources and verification boundaries

Framework requirements were checked against official documentation on 4 October 2026. Exact package versions and runtime compatibility must be resolved again when development starts.

- [Laravel 13 deployment and server requirements](https://laravel.com/docs/13.x/deployment).
- [Filament 5 installation requirements](https://filamentphp.com/docs/5.x/introduction/installation).
- [Inertia server-side rendering](https://inertiajs.com/server-side-rendering).
- [Nigeria Revenue Service VAT information](https://www.nrs.gov.ng/tax-information/value-added-tax), as a starting point for the tax review workflow.
- [Nigeria Tax Act 2025 — National Assembly publication](https://nass.gov.ng/documents/download/11249), for professional review of applicable provisions.
- [Nigeria Tax Administration Act 2025 — NRS publication](https://www.nrs.gov.ng/uploads/NIGERIA_TAX_ADMINISTRATION_ACT_2025_8c945071a7.pdf), for professional review of administration requirements.

The tax sources above are references to review, not a completed interpretation of rates, exemptions, payroll rules or deadlines. This specification deliberately supplies no production statutory rate table. ALCO's designated qualified reviewer must approve applicable configurations and tax articles before public launch.
