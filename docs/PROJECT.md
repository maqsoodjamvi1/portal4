# School Management System

CodeIgniter 4 application for multi-campus school operations: admin panel, parent/student portal, fees, exams, quizzes, Hifz program, trial signup, and RBAC.

## Requirements

- PHP 8.2+
- MySQL
- Composer
- Web server document root: `public/`

## Setup

```bash
composer install
cp env .env
# Edit .env: database, app.baseURL, encryption.key
php spark migrate
php spark db:seed DatabaseSeeder
```

Optional Quran reference data:

```bash
php spark db:seed QuranReferenceSeeder
php spark db:seed QuranAyahSeeder
```

## Run locally

```bash
php spark serve
```

## Security notes

- Portal login requires parent/student passwords (`app/Controllers/Frontend/Auth.php`).
- Admin sessions expire after 8 hours (`app/Config/Session.php`).
- Run migrations on deploy; Hifz `autoMigrate` is off in production (`app/Config/Hifz.php`).
- Never commit `.env`; point Apache/Nginx to `public/` only.

## Tests

Default suite (no database; works without SQLite3):

```bash
vendor/bin/phpunit
```

Coverage (requires Xdebug or PCOV on the machine):

```bash
vendor/bin/phpunit -c phpunit.coverage.xml.dist
```

Optional CI4 scaffold integration tests (SQLite3 **or** `database.tests.*` in `phpunit.integration.xml.dist`):

```bash
vendor/bin/phpunit -c phpunit.integration.xml.dist
```

## SQL safety

- Prefer `$db->table()` query builder or `$db->query($sql, [$bindings])`.
- Use `App\Libraries\SafeQuery` for campus/system lookups and bulk `whereIn` deletes.
- Helpers: `safe_query_helper.php` (`searchParentsByName`, `searchStudentsByName`, etc.).
- Remaining legacy CI3 controllers may still use raw SQL; migrate when touching those files.

## Route modules

- `app/Config/Routes.php` — main registry
- `app/Config/Routes/StudentPortal.php` — parent/student portal
- `app/Config/Routes/AdminHifz.php` — Hifz program
- `app/Config/Routes/AdminLegacy.php` — legacy `admin` dispatcher (deprecated)

## Key libraries

| Area | Location |
|------|----------|
| Admin RBAC | `app/Filters/AdminPermissionFilter.php` |
| Portal auth | `app/Libraries/PortalAuthService.php` |
| Hifz domain | `app/Libraries/Hifz*.php` |
| Trial signup | `app/Libraries/TrialProvisioningService.php` |
