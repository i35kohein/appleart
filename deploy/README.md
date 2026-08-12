# Apple Art React — production deploy (replace PHP UI, keep PHP backend)

## How it works

`dist/` is a static SPA. It calls the PHP backend at same-origin `/api/*` (exactly
like the Vite dev proxy). So **no CORS, no rewrite of API paths** — just serve
`dist/` from the same web root that already has `api/`, `config.php` and `uploads/`.

The PHP `api/*.php` files still handle auth sessions, DB and uploads. The React app
is only the new front-end shell.

## Option A — Apache / cPanel (recommended)

1. Build: `npm run build` (outputs to `dist/`)
2. Upload the **contents of `dist/`** into your web root, next to the existing
   `api/` folder (same level as `index.php`, `config.php`, `uploads/`).
   Do **not** delete `api/`, `uploads/`, `config.php`.
3. Copy the included `.htaccess` (from `deploy/.htaccess`) into the web root.
   It rewrites all non-file, non-`/api/` requests to `index.html` so React Router
   deep links (`/students/3`, `/calendar`, …) work on refresh.
4. That's it. `/api/get_students.php` etc. keep working unchanged.

The old `index.php` becomes the "old UI" — you can keep it as `old-index.php`
for rollback, or delete it once the new UI is verified.

## Option B — same domain, keep old UI reachable

Serve the SPA under a sub-path (e.g. `/new/`). Requires setting Vite `base: '/new/'`
and rebuilding. Not recommended unless you want both UIs live side by side for a
while.

## Option C — subdomain (e.g. app.example.com)

Point a subdomain at a folder containing `dist/` contents + a copy of `api/`,
`config.php`, `uploads/` (or an alias to the existing ones). Sessions stay
same-origin, so auth just works. This is the cleanest isolation if you ever want
to migrate the API to another host.

## Local production smoke test

```bash
npm run build
npx vite preview          # serves dist/ on :4173; proxies /api -> :8088 (PHP) via preview.proxy
```

## Rollback

The old PHP UI is untouched in git (`appleart 2` folder). To roll back, just
restore the original `index.php` / remove the new `index.html` + `assets/`.

## Notes

- Session cookie: `credentials: 'include'` is used everywhere; PHP sessions work
  as long as SPA + API share the origin (all options above do).
- Photo uploads: React shows `/uploads/<filename>` — same folder the PHP upload
  endpoints write to.
- First paint: ~72 KB gzip JS (code-split; each page loads its own small chunk).
