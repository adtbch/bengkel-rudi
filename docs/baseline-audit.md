# Baseline Audit — portofolio-bengkel-rudi (opt/optimize-bengkelrudi)

## Branch
- Created `opt/optimize-bengkelrudi` from `master` (clean).

## Tests
- phpunit: 69 tests, 8 failures (6 errors + 2 failures), pre-existing. Quality gate per service: must not add regressions.

## Backend / DB
- Eager loading already present on home/portfolio index (`with(['service','images'])`).
- Missing indexes: `services.is_active`, `services.sort_order`, `portfolios.service_id` (FK index auto on MySQL), `portfolio_images.portfolio_id` (FK auto). Add targeted indexes only where measured.
- Homepage cached 60s (`public.homepage_data`), skipped in testing.

## Frontend / Views
- 22 Blade views. Public: home, about, contact, services index/detail, portfolio index/detail + layouts/partials.
- CWV not yet measured (LCP/CLS/INP, mobile-first) — needs Lighthouse run.

## Deferred homepage work
- Consolidate or build public CSS (`base.css`, `components.css`, `header.css`, `footer.css`, `home.css`) only after Lighthouse confirms request/render-blocking impact.
- Inspect `resources/js/app.js` and defer every non-critical script only after confirming no public-page behavior depends on synchronous execution.

## Cloudinary
- Used in admin upload + service. Public usage needs image sizing/lazyload coordination.

## Ranked bottlenecks (to verify with Lighthouse)
1. Public frontend image delivery (Cloudinary sizing/lazyload).
2. Missing DB indexes on filter/sort columns.
3. Bundle size / Vite assets.
4. Admin views lower priority.
" path">
D:/Kerja/Portofolio bengkel/portofolio-bengkel-rudi/docs/baseline-audit.md