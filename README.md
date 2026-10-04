# ALCO Associate Solutions website

Laravel 13 application for ALCO Associate Solutions. The public site uses Inertia 3, Vue 3, TypeScript, Tailwind CSS 4 and server-side rendering. The content management panel uses Filament 5 at `/admin`. The local database is MySQL.

The client selected design **Option 2** in `docs/design-options/`. Its responsive public homepage is implemented with the navy and cyan visual style, consultation imagery, service navigation, and desktop/mobile menus. The project specification is `docs/ALCO_WEBSITE_DEVELOPMENT_PLAN.md`.

## Local setup (Laragon on Windows)

1. Start Laragon's web server and MySQL.
2. In `C:\laragon\www\alco`, run `composer install` and `npm ci`.
3. Copy `.env.example` to `.env` if needed and configure a local MySQL database. Never commit `.env` or an application key.
4. Run `php artisan key:generate` for a fresh environment, then `php artisan migrate`.
5. Run `npm run build:ssr` for production-style client and SSR bundles. For active frontend work, run `npm run dev` and keep the SSR process available when testing server-rendered pages.
6. Open `http://alco.test` when Laragon's automatic virtual hosts are active. A new folder may need Laragon to reload its hosts. For an immediate local smoke test, run `php artisan serve` and open `http://127.0.0.1:8000`; adjust `APP_URL` in `.env` for that mode if generated links need the same host. Set a manually configured web host's document root to this project's `public` directory.

This local checkout already has a dedicated `alco` MySQL database, `alco_local` account, configured `.env`, application key and completed migrations. The password is stored only in the ignored `.env` file.

## Initial admin account

Run `php artisan alco:make-admin` in an interactive terminal. The command asks for the administrator's name, email and a password of at least 12 characters; it does not take the password as a command argument. Sign in at `http://alco.test/admin`. Public registration is disabled and the Filament panel admits only designated admin users. No admin account has been created yet because an approved email address was not supplied.

The starter also includes Laravel Fortify login, password reset, email verification, 2FA and passkey components. Their final staff workflow will be reviewed as CMS roles are implemented. SMTP is still set to the local log mailer; configure a real provider before relying on email verification or password reset outside development.

## Checks

- `php artisan test`
- `npm run check`
- `npm run types:check`
- `npm run build:ssr`
- `composer run types:check`
- `composer run lint:check`

The Laravel starter's client and SSR builds are generated under ignored `public/build` and `bootstrap/ssr`. In production, supervise the Inertia SSR process and queue worker, run the Laravel scheduler and point Nginx at `public` as described in the project plan.

## Current scope

Foundation and scaffolding are in place: application, frontend toolchain, CMS panel, MySQL connection, admin access gate, source logo, design references and planning document. Option 2's homepage and public navigation have been implemented. The About, Services, Insights, Tools, Contact and consultation destinations currently have initial content so every navigation link works; they are marked `noindex` while being developed. Insights publishing, calculators, lead forms and CMS-managed page content are subsequent implementation phases. The consultation page currently provides the supplied phone numbers for enquiries.

The hero photograph was generated for this project from a prompt for three Nigerian professionals reviewing financial documents in a contemporary Lagos office. It is served as an optimized WebP at `public/images/hero-consultation.webp`. The white navigation mark is a small SVG reconstruction of the supplied ALCO logo, while the original JPEG remains in `public/brand/` for reference.
