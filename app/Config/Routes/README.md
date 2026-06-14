# Route modules

`app/Config/Routes.php` is the bootstrap only (defaults, home, require chain).

## Load order

1. `PublicRoutes.php` — uploads, trial signup, captcha
2. `AdminPriority.php` — early explicit admin URLs (question bank, campus mgmt, face attendance, etc.)
3. `AdminHealthSalary.php` through `AdminMisc.php` — domain admin groups
4. `AdminHifz.php` — Hifz program
5. `AdminReports.php` — P&L, strength, daily reports, fee reminders
6. `AdminBilling.php` — billing & plan admin (legacy snake_case URLs; class names must match Linux casing)
7. `AdminLegacy.php` — **last** admin catch-all (`AdminDispatcher`, `Fallback`)
8. `StudentPortal.php` — `/student/*` portal

## Regenerating from backup

If you need to re-split from the monolith:

```bash
php tools/split_routes.php
```

Requires PHP CLI (e.g. `c:\xhamp\php\php.exe`). Creates `Routes.php.bak-split` before overwriting.

## Adding routes

Add new routes to the smallest matching domain file, or create a new `Admin*.php` and `require` it **before** `AdminLegacy.php`.
