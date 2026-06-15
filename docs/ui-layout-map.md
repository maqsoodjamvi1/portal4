# UI layout map (main routes)

Generated for QA. Views with `extend('layouts/admin_template')` use the admin shell unless noted.

## Cross-shell exceptions (fixed)

| View | Layout | Audience |
|------|--------|----------|
| `admin/quizzes/quiz_selector.php` | `layouts/admin_template` | Admin |
| `frontend/family_diary_whatsapp.php` | `frontend/layouts/master_portal` | Parent share |
| `frontend/dashboard/parent.php` | `frontend/layouts/master_portal` (+ hub flag) | Parent |

## Portal (`frontend/layouts/master_portal`)

- `frontend/dashboard/student.php`
- `frontend/dashboard/parent.php` (hub)
- `frontend/quizzes/*` (most)
- `frontend/fees/index.php`, `attendance/index.php`, `profile/index.php`, `results/index.php`

## Standalone (no admin_template)

- `admin/login.php`
- `trial_signup/*`
- `parent/attendance_shared.php` (Bootstrap 5 CDN — share link only)
- Print/PDF under `admin/chalanview/`, `admin/printchalanview/` (minimal chrome)

## Legacy

- `AdminDispatcher` → `admin/layout.php` wrapping raw HTML

## Regenerate hints

```bash
rg "extend\('" app/Views --no-heading | sort
rg "return view\(" app/Controllers --no-heading
```
