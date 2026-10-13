# ALCO Associate Solutions website

Laravel 13 application for ALCO Associate Solutions. The public site uses Inertia 3, Vue 3, TypeScript, Tailwind CSS 4 and server-side rendering. The content management panel uses Filament 5 at `/admin`. The local database is MySQL.

The client selected design **Option 2** in `docs/design-options/`. Its responsive public homepage is implemented with the navy and cyan visual style, consultation imagery, service navigation, and desktop/mobile menus. The project specification is `docs/ALCO_WEBSITE_DEVELOPMENT_PLAN.md`.

The owner has confirmed `https://alcosolutions.ng` as the intended production domain. Public enquiries go to `alcosolutionsng@gmail.com` (listed first) and `info@alcosolutions.ng`; both are editable in Admin → Site settings. The local `alco.test` address remains separate; production DNS, hosting, SSL and SMTP have not yet been configured here.

## Local setup (Laragon on Windows)

1. Start Laragon's web server and MySQL.
2. In `C:\laragon\www\alco`, run `composer install` and `npm ci`.
3. Copy `.env.example` to `.env` if needed and configure a local MySQL database. Never commit `.env` or an application key.
4. Run `php artisan key:generate` for a fresh environment, then `php artisan migrate`.
5. Optional: run `php artisan db:seed --class=LocalContentSeeder` to add provisional homepage and four service records for local review. This seeder refuses non-local environments and does not overwrite existing records.
6. Run `npm run build:ssr` for production-style client and SSR bundles. For active frontend work, run `npm run dev` and keep the SSR process available when testing server-rendered pages.
7. Open `http://alco.test` when Laragon's automatic virtual hosts are active. A new folder may need Laragon to reload its hosts. For an immediate local smoke test, run `php artisan serve` and open `http://127.0.0.1:8000`; adjust `APP_URL` in `.env` for that mode if generated links need the same host. Set a manually configured web host's document root to this project's `public` directory.

This local checkout already has a dedicated `alco` MySQL database, `alco_local` account, configured `.env`, application key and completed migrations. The password is stored only in the ignored `.env` file.

## Initial admin account

Run `php artisan alco:make-admin` in an interactive terminal. The command asks for the administrator's name, email and a password of at least 12 characters; it does not take the password as a command argument. Sign in at `http://alco.test/admin`. Public registration is disabled and the Filament panel admits only designated admin users. No admin account has been created yet; the general enquiries address is not assumed to be a staff login.

The starter also includes Laravel Fortify login, password reset, email verification, 2FA and passkey components. Their final staff workflow will be reviewed as CMS roles are implemented. SMTP is still set to the local log mailer; configure a real provider before relying on email verification or password reset outside development.

## First CMS content slice

Filament now has **Pages** and **Services** resources. The Pages resource currently supports the homepage template; Services supports the four core service records and new draft services. Staff save draft fields, then use **Publish saved draft** when ready. The public site reads a saved publication snapshot, so later draft edits stay private until republished. Each publication creates a history snapshot. Archived and future-dated records are excluded from public routes; published service slugs have individual pages.

The local database contains provisional sample copy for visual review. It is not tax advice or final client-approved copy. Review all service scope, audience, process and FAQ wording before a production release. At this stage every designated admin can publish; finer editor/reviewer/publisher roles, preview links, content media and the remaining page templates are still planned.

## Checks

- `php artisan test`
- `npm run check`
- `npm run types:check`
- `npm run build:ssr`
- `composer run types:check`
- `composer run lint:check`

The Laravel starter's client and SSR builds are generated under ignored `public/build` and `bootstrap/ssr`. In production, supervise the Inertia SSR process and queue worker, run the Laravel scheduler and point Nginx at `public` as described in the project plan.

## Current scope

Foundation and scaffolding are in place: application, frontend toolchain, CMS panel, MySQL connection, admin access gate, source logo, design references and planning document. Option 2's homepage and public navigation are implemented. The homepage sections, core service cards and service detail pages now read published CMS content. Insights now has CMS-managed articles, categories, tags and topic hubs, with publication snapshots, category archives, search, pagination and related-content links. The local VAT material is deliberately draft-only until a qualified reviewer approves the wording, sources and applicable dates. Tools and their reviewed configuration workflow remain the next major phase.

The hero photograph was generated for this project from a prompt for three Nigerian professionals reviewing financial documents in a contemporary Lagos office. It is served as an optimized WebP at `public/images/hero-consultation.webp`. The white navigation mark is a small SVG reconstruction of the supplied ALCO logo, while the original JPEG remains in `public/brand/` for reference.
