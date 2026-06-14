# Worktree Audit

Last reviewed: 2026-06-15

This repository currently has a very large active worktree. The cleanup pass did
not revert application changes. It separated local/runtime noise from real
product work so the remaining status can be reviewed deliberately.

## Current shape

`tools/git-status-summary.ps1` reports file-level status:

- 1324 modified tracked files
- 785 untracked files
- Most changes are under `app` and `public`
- Local-only runtime/vendor/env noise is hidden with `skip-worktree`

The largest changed areas by path are:

- `app/Views/admin` - 686 files
- `app/Controllers/Admin` - 343 files
- `app/Views/frontend` - 119 files
- `app/Controllers/Frontend` - 47 files
- `app/Database/Migrations` - 39 files
- `public/assets/js` - 28 files
- `public/assets/css` - 24 files

Current review-bucket counts from `tools/git-status-summary.ps1 -Buckets`:

- hygiene/tooling - 5
- shared UI compatibility - 3
- routing/admin shell - 39
- admin modules - 1227
- portal/frontend modules - 194
- new domains/migrations - 130
- asset duplication review - 209
- core/shared app infrastructure - 259
- legacy/public resources - 16
- deploy/scripts/manual ops - 24
- project documentation - 3

## Review buckets

Use these buckets when turning the dirty tree into professional commits:

1. Git hygiene and local tooling
   - `.gitattributes`
   - `.gitignore`
   - `docs/git-worktree-hygiene.md`
   - `docs/worktree-audit.md`
   - `tools/git-status-summary.ps1`

2. Shared UI compatibility
   - `public/assets/css/school-forms.css`
   - `public/assets/js/bootstrap5-compat.js`
   - `app/Views/layouts/header.php`

3. Routing and shell structure
   - `app/Config/Routes.php`
   - `app/Config/Routes/`
   - `app/Views/layouts/partials/`
   - `app/Libraries/AdminMenuBuilder.php`
   - `app/Libraries/Menu/`

4. Admin feature modules
   - controllers in `app/Controllers/Admin/`
   - views in `app/Views/admin/`
   - models and libraries needed by those modules

5. Portal/frontend modules
   - controllers in `app/Controllers/Frontend/`
   - parent/student portal views in `app/Views/frontend/`
   - `app/Controllers/Parent/`

6. New domains and migrations
   - BoardPrep
   - Hifz
   - question papers
   - crossword/word search/math worksheet
   - role/menu access
   - salary/campus finance

7. Asset duplication review
   - `app/assets/`
   - `public/assets/`
   - `public/leaving_certificate/assets/`

8. Core/shared app infrastructure
   - config, filters, helpers, language files, shared libraries
   - shared layout/components/errors
   - composer/phpunit/test support changes

9. Legacy/public resources
   - `public/resource/`
   - legacy static entry files

10. Deploy/scripts/manual ops
    - `scripts/`
    - deploy/database/admin utilities under `tools/`

11. Project documentation
    - non-hygiene documentation under `docs/`

## Recommended commit order

1. Commit hygiene/tooling first.
2. Commit the shared select/toggle compatibility fix next.
3. Commit route splitting and admin shell/menu work before feature modules.
4. Commit each domain module with its migrations, services, controllers, views,
   assets, and tests together.
5. Leave generated, deployment, backup, and copied asset trees out unless each
   file is intentionally needed by the runtime.

## Useful commands

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -ShowLocalOnly
git status --short -- app public
git diff --stat -- app public
git ls-files --others --exclude-standard
```
