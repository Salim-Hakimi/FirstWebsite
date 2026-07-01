# Fanous Frontend Architecture

## Current Analysis

- Backend: Laravel 12 with PHP 8.2+.
- Authentication: Laravel session-based authentication with CSRF protection.
- Authorization: role middleware and model role helpers.
- Current UI: Blade templates, custom CSS, Corona admin CSS, Tailwind/Vite assets, RTL Persian/Dari layout.
- API status: the project does not currently expose a complete REST API. Most routes return Blade pages or handle traditional form posts.

## Recommendation

Use Vue 3 + Vite as a progressive frontend layer instead of replacing all Blade pages at once.

Reasons:

- It keeps the existing backend routes, authentication, roles, permissions, validation, and business logic safe.
- It avoids a risky full SPA rewrite before API endpoints exist.
- It allows migrating high-value screens page by page.
- It keeps initial JavaScript small by lazy-loading Vue components only when mounted.

## Implemented Structure

```text
frontend/src/
  App.vue
  main.js
  assets/
  components/
    common/
    layout/
  composables/
  pages/
  router/
  services/
  stores/
  styles/
```

## How To Mount Vue On A Blade Page

```blade
<div data-vue-app="spa"></div>
```

For async API cards:

```blade
<div
    data-vue-app="dashboard-card"
    data-vue-context='{"title":"Example"}'
    data-endpoint="/some-json-endpoint"
></div>
```

## API Strategy

- `frontend/src/services/api.js` is the central Axios client.
- It uses same-origin requests by default.
- It reads the CSRF token from `<meta name="csrf-token">`.
- It uses `X-Requested-With: XMLHttpRequest`.
- It redirects 401 responses to `/login`.

## Migration Plan

1. Keep existing Blade pages as the stable production UI.
2. Add small Vue components to pages that need smoother interactions.
3. Create JSON endpoints only when a page is ready to migrate.
4. Keep backend validation as the source of truth.
5. Migrate large tables to API-backed pagination/search gradually.
6. Move only proven stable pages into Vue Router routes.

## Commands

```bash
npm install
npm run dev
npm run build
php artisan test
```
