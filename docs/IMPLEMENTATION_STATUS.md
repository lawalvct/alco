# ALCO implementation status

Updated: 5 October 2026. This checklist tracks `ALCO_WEBSITE_DEVELOPMENT_PLAN.md`; it is a development record, not client approval of public claims.

## Completed in the local project

- Laravel 13, Inertia/Vue/TypeScript/Tailwind public site, Filament 5 admin, MySQL and local SSR foundation.
- Selected Option 2 responsive theme, navigation, hero and core service presentation.
- Confirmed domain `alcosolutions.ng` and enquiries address `info@alcosolutions.ng` recorded in the project.
- First CMS slice: homepage and Service models/resources, draft content separate from published snapshots, publication history, admin-only policies and public service detail routes.
- Provisional local-only homepage and four-service content set for visual review. No production seed data or admin account is created by this seed.

## Next implementation work

1. Review and refine the provisional homepage, service scope and FAQ copy with ALCO. Fill About and leadership content only from approved facts.
2. Complete Phase 2 resources: remaining Pages/templates, Team, FAQs, menus/site settings and media. Add granular editor/reviewer/publisher permissions and draft previews before production publication.
3. Build Insights editorial workflow and the VAT content hub, then a configurable, reviewed VAT calculator as the first tool.
4. Extend the tools platform to all eleven calculators; build lead forms, notifications and management after the first end-to-end slice is verified.
5. Complete SEO/schema, analytics, accessibility, security, staging, deployment and handover checks in the plan.

## Client review still needed

- Final service descriptions, scope exclusions and intended audiences.
- Leadership roles, bios, portraits and any credentials intended for display.
- Verified social profile URLs, approved legal/privacy text, and preferred recipient for form notifications.
- Qualified tax reviewer and approved source/rate settings before tax content or calculators are published.

The sample copy is locally visible to make the site reviewable. Its publication state in the local database is not approval for deployment. The `LocalContentSeeder` refuses to run outside `APP_ENV=local`.
