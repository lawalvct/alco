# ALCO deployment checklist

For the aaPanel / Nginx VPS described in section 16 of `ALCO_WEBSITE_DEVELOPMENT_PLAN.md`. Tick each item on staging first, then production.

## 1. Server

- [ ] PHP 8.3+ with `gd` (WebP and AVIF support), `intl`, `exif`, `mbstring`, `pdo_mysql`, `bcmath`, `fileinfo`
- [ ] MySQL 8+, a database user with only the rights it needs, utf8mb4
- [ ] Node (a supported LTS) for the build and the Inertia SSR process
- [ ] Nginx document root is the app's `public` directory; hidden files and the repository root are not served
- [ ] HTTPS enabled; Cloudflare in front with the origin certificate

## 2. Environment (`.env`, never committed)

| Variable | Value |
| --- | --- |
| `APP_ENV` | `production` (this also lets search engines crawl; `robots.txt` blocks everything otherwise) |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://alcosolutions.ng` (used in the sitemap, canonical tags and structured data) |
| `APP_KEY` | generate once with `php artisan key:generate`; back it up |
| `DB_*` | production database |
| `QUEUE_CONNECTION` | `database` |
| `MAIL_*` | real SMTP details; `MAIL_FROM_ADDRESS` on the `@alcosolutions.ng` domain |
| `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` | from Cloudflare; both empty switches the check off |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | only for creating the first admin, then remove |

Mail: set up SPF, DKIM and DMARC for the domain. Without them, enquiry emails are likely to land in spam.

## 3. First release

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build:ssr
php artisan migrate --force
php artisan storage:link
php artisan alco:make-admin          # interactive; creates the first administrator
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

The migrations create the starting menus and **unpublished** drafts of the privacy notice, terms and calculator disclaimer.

## 4. Background processes (supervised, restarted after each release)

- [ ] Queue worker: `php artisan queue:work --tries=3 --timeout=60`. Without it, enquiry emails and image optimisation wait in the queue.
- [ ] Inertia SSR: `php artisan inertia:start-ssr`, bound to localhost only (port 13714). Check with `php artisan inertia:check-ssr`.
- [ ] Scheduler: cron every minute running `php artisan schedule:run`.
- [ ] After each release: `php artisan queue:restart` and restart SSR.

## 5. Content to publish before launch (admin → Website)

- [ ] Homepage and About pages: review the wording
- [ ] **Legal pages:** edit and publish Privacy notice, Terms of use and Calculator disclaimer. They contain `[ALCO to confirm]` placeholders and must be approved by ALCO, with legal advice where needed. They are hidden from the site until published.
- [ ] Services and Packages: approve the wording; confirm anything about price or terms before adding it
- [ ] Team: names, confirmed roles, bios and photos
- [ ] Homepage slider: replace sample images with approved ones, confirm usage rights, add alt text, run `php artisan alco:optimise-media` once
- [ ] Site settings: confirm addresses, phone numbers, WhatsApp, social profile URLs (leave empty until checked) and who receives enquiry emails
- [ ] Calculators: review each tool's search title, description, guide, worked example and questions in `/admin/calculators`; confirm the tax wording with a qualified reviewer
- [ ] Tax calculator rules: add only sourced, dated and approved rules; until then visitors see manual illustrative rates
- [ ] Sample or provisional content: the local seeder is not run in production; check nothing placeholder is published

## 6. Checks after deploying

- [ ] `https://alcosolutions.ng/robots.txt` allows crawling and shows the sitemap; `/sitemap.xml` lists only live pages
- [ ] Each `/tools/{slug}` page and `/tools` returns HTTP 200 to a signed-out visitor; complete a sample calculation without logging in
- [ ] View source on `/tools/vat` and another calculator: the HTML contains a distinct `<title>`, meta description, canonical URL, visible heading and guide text. If it contains only the Inertia app shell, start or repair SSR before launch
- [ ] Check the calculator JSON-LD against its visible name, description and free access; use Google's Rich Results Test without assuming a rich result or ranking
- [ ] Submit one test enquiry on each form: it appears under Enquiries, the staff email arrives, and the reference shows on the thank-you screen
- [ ] View source on the homepage: the `AccountingService` JSON-LD matches the visible contact details
- [ ] Admin login works; turn on two-factor sign-in for every administrator
- [ ] `php artisan about` shows production, debug off, caches built
- [ ] Add the site to Google Search Console and Bing Webmaster Tools, and submit the sitemap

## 7. Backups and recovery

- [ ] Daily encrypted off-site backup of the database and `storage/app/public` (uploaded media)
- [ ] Restore tested on staging; agree retention and recovery time with ALCO
- [ ] Keep the previous release directory so code can be rolled back; database changes need their own plan

## Not built yet (from the development plan)

Staff roles beyond a single admin switch, redirects manager, resources downloads, analytics, audit history, CSP headers. Track these before relying on them.
