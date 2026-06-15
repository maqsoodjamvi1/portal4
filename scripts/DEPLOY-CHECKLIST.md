# Admin shell deploy checklist

After menu or admin layout changes, upload together:

- `app/Libraries/AdminMenuSections.inc.php` (run `php spark menu:build` first)
- `app/Libraries/Menu/` (all partials)
- `app/Views/layouts/header.php`
- `app/Views/layouts/admin_template.php`
- `public/assets/css/admin-command-palette.css`

Smoke test on production:

1. `admin/dashboard` — UI only (no raw PHP / `$examsItems`)
2. Ctrl+K opens command palette
3. Sidebar order: Dashboard → Profiles → Sessions
