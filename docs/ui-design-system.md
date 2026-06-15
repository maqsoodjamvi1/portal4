# UI Design System

CodeIgniter 4 views use **AdminLTE 3** with **Bootstrap 5** loaded in the shared shells. New work should use shared tokens and components under `app/Views/components/` and `public/assets/css/design-tokens.css`.

Legacy pages may still contain legacy Bootstrap-era markup and jQuery calls such as `$('#modal').modal('show')`; `public/assets/js/bootstrap5-compat.js` bridges common patterns during the migration, including legacy data attributes, jQuery plugin calls, panels, input group addons, glyphicons, custom controls, and close buttons. Select2, DataTables, and Summernote view assets now use Bootstrap 5-compatible themes/adapters where available. Date fields use Flatpickr plus `public/assets/js/datetimepicker-compat.js`, which preserves the legacy jQuery `datetimepicker()` API while views/controllers are gradually migrated to direct Flatpickr or native date controls.

## Layout shells

| Shell | Extend |
|-------|--------|
| Admin (default) | `layouts/admin_template` |
| Student / parent portal | `frontend/layouts/master_portal` |
| Parent dashboard hub | `frontend/layouts/master_portal` with `portalHubParent => true` (or legacy alias `layouts/master_portal`) |

### Layout sections

- `pageStyles` — page CSS
- `content_header` — optional inner header (prefer `components/page_header` in `content`)
- `content` — main body
- `modals` — page modals (prefer `components/modal_shell`)
- `pageScripts` — JS; set `uiNeedsDataTables` / `uiNeedsSummernote` when needed

## Design tokens (`design-tokens.css`)

Use `var(--sms-*)` for colors, radius, spacing, and typography. Do not add new hard-coded hex values in views unless required for print/PDF.

| Token | Usage |
|-------|--------|
| `--sms-primary` | Buttons, links, admin bar |
| `--sms-surface` | Cards, modals |
| `--sms-border` | Dividers, inputs |
| `--sms-radius` | Cards, filter panels |
| `--sms-text-muted` | Help text, subtitles |

## Components (`app/Views/components/`)

| Component | Purpose |
|-----------|---------|
| `page_header.php` | Title, subtitle, breadcrumbs, actions |
| `filter_card.php` | Filter forms (wraps report filter pattern) |
| `data_table_card.php` | List card with toolbar and table slot |
| `form_field.php` | Label + control + validation message |
| `modal_shell.php` | Accessible Bootstrap 5 modal |
| `empty_state.php` | No data / empty lists |
| `confirm_dialog.php` | Documented SweetAlert helper (JS) |
| `report_filter_bar.php` | Legacy; prefer `filter_card` for new reports |

## Forms

- Add class `needs-validation` and `novalidate` on forms that submit via POST.
- Use `form_field` for required fields.
- AJAX forms: call `SmsFormValidation.applyServerErrors(form, errors)` from `assets/js/sms-form-validation.js`.

## Modals

- Use `modal_shell` with `id`, `title`, `body` (HTML string or view), `footer` optional.
- Fee/student modals: set `fullscreenMobile => true`.
- Include `aria-labelledby` and focusable close control.

## Lists and reports

- Lists: `data_table_card` + DataTable in `pageScripts`; set `$uiNeedsDataTables = true` in view or controller.
- Reports: `filter_card` + `report-ui.css` / `components-ui.css`.

## Asset loading

Pass from controller or at top of view:

```php
$uiNeedsDataTables = true;  // default true in header for backward compatibility
$uiNeedsSummernote = false;
$uiNeedsChart = false;
```

## RTL

Admin header loads `adminlte.rtl.min.css` for `ar` and `ur`. Prefer logical properties (`margin-inline-start`) in new CSS.

## Pilot modules (reference implementation)

- Roles: `admin/roles/index.php`, `admin/roles/form.php`
- Hifz: `admin/hifz/*/index.php`
- Report: `admin/reports/student_fee_summary.php`
- Faculty setup: `admin/users.php`, `admin/users_edit.php`
- Academic setup: `admin/classes.php`, `admin/subjects.php`, `admin/subjects_edit.php`
- Finance: `admin/expenses.php`, `admin/expense_head.php`, `admin/expense_head_edit.php`, `admin/expense_report_by_head.php`
- Students: `admin/students.php`, `admin/students_edit.php`, `admin/students_print.php`, `admin/students_results_list.php`, `admin/students_defaulters_list.php`, `admin/students_list.php`, `admin/enroll_students.php`, `admin/student_class.php`, `admin/student_class_edit.php` (list pages use `filter_card` + `data_table_card`)
- Fees: `admin/fee_chalan.php`, `admin/fee_chalan_pay.php`, `admin/fee_chalan_balance.php`, `admin/fee_chalan_daily_collection.php`, `admin/fee_chalan_edit.php`, `admin/fee_collection_sessions.php`, `admin/fee_setup/index.php`, `admin/fee_plan_months/index.php`, `admin/fee_type.php`, `admin/fee_type_edit.php`, `admin/fee_history_report.php`, `admin/advance_fee/index.php`, `admin/parents_paidfee.php`, `admin/parents_balancefee.php`
- Reports: `admin/profit_loss_report.php`, `admin/defaulter_students_fee_report.php`, `admin/family_fee_report.php`, `admin/single_student_fee_report.php`, `admin/student_fee_report.php`, `admin/student_report_by_fee_type.php`, `admin/monthly_expense_report.php`, `admin/attendance_report.php`, `admin/class_section_strength_report.php`, `admin/student_daily_report.php`, `admin/student_weekly_non_present_report.php`, `admin/timetable_report.php`
- Academic: `admin/grades.php`, `admin/grades_setup.php`, `admin/expenses_edit.php`, `admin/question_bank_report.php`
- Attendance: `admin/students_attendance.php`, `admin/students_attendance_edit.php`, `admin/students_attendance_detail.php`, `admin/students_attendance_report.php`, `admin/employees_attendance.php`, `admin/employees_attendance_edit.php`, `admin/attendance_monthlyreport.php`, `admin/attendance_termreport.php`, `admin/attendance_working_days_report.php`, `admin/emp_attendance_monthlyreport.php`
- Results & exams: `admin/students_results.php`, `admin/students_results_edit.php`, `admin/students_results_compilation.php`, `admin/students_subject_results_edit.php`, `admin/classwise_results.php`, `admin/exam.php`, `admin/exam_add.php`, `admin/exam_edit.php`
- Quizzes & QB: `admin/quizzes/index.php`, `admin/quizzes/index_cards.php`, `admin/quizzes/create.php`, `admin/quizzes/edit.php`, `admin/quizzes/edit_questions.php`, `admin/quizzes/results.php`, `admin/question_bank_list.php`, `admin/question_bank_form.php`, `admin/question_bank_ai_generate.php`, `admin/question_bank_proof_read.php`, `admin/question_paper/index.php`, `admin/question_bank_gk.php`, `admin/question_bank_gk_edit.php`, `admin/question_bank_gk_ur.php`, `admin/question_bank_gk_ur_edit.php`, `admin/question_text_mcq.php`, `admin/question_text_mcq_edit.php`, `admin/quiz.php`, `admin/quiz_edit.php`, `admin/quiz_ai.php`, `admin/quiz_builder.php`, `admin/quiz_questions.php`, `admin/quiz_questions_add.php`, `admin/quiz_questions_edit.php`, `admin/question_quiz.php`, `admin/question_quiz_edit.php`
- HR & profile: `admin/salary_settings.php`, `admin/salary_reports.php`, `admin/salary_bulk_adjustment.php`, `admin/salary_slips_list.php`, `admin/advance_salary.php`, `admin/profile.php`, `admin/profile_campus.php`
- Hifz: `admin/hifz/recitation/index.php`
- Datesheet: `admin/datesheet.php`, `admin/datesheet_edit.php`, `admin/datesheet_classwise.php`, `admin/datesheet_syllabus.php`, `admin/datesheet_report_edit.php`, `admin/datesheet_section_subjects_edit.php`, `admin/students_w_datesheet_list.php`
- Class diary: `admin/classdiary.php`, `admin/classdiary_edit.php`, `admin/classdiary_view.php`, `admin/classdiary_bagpack.php`, `admin/diary_analytics/index.php`
- Chalan: `admin/chalanview/chalan_filter.php`, `admin/chalanview/add.php`, `admin/chalanview/edit.php`, `admin/print_fee_chalan.php`, `admin/chalanview/fee_chalan_*.php`, `admin/printchalanview/fee_chalan_*.php`, `admin/fee_chalan_month.php`, `admin/add_all_chalan.php`, `admin/fee_chalan_document.php`, `admin/delete_fee_chalan_edit.php`
- Academic setup: `admin/getting_started.php`, `admin/academic_setup.php`, `admin/academic_session.php`, `admin/academic_session_edit.php`, `admin/academic_calendar_builder.php`, `admin/classes_edit.php`
- Timetable: `admin/timetable_add.php`, `admin/timetable_generator.php`, `admin/timetable_report.php`
- Campus & profile: `admin/campus.php`, `admin/campus_edit.php`, `admin/campus_management.php`, `admin/campus_settings.php`, `admin/campus_finance_accounts/index.php`, `admin/profile.php`, `admin/profile_campus.php`, `admin/profile_system.php`, `admin/profile_student.php`
- Teachers & planning: `admin/teachers.php`, `admin/teachers_edit.php`, `admin/teacher_subjects.php`, `admin/teacher_subjects_edit.php`, `admin/teacher_section.php`, `admin/teacher_section_edit.php`, `admin/school_timing.php`, `admin/school_timing_edit.php`, `admin/emp_timing_edit.php`, `admin/top_level_planning.php`, `admin/top_level_planning_edit.php`, `admin/top_level_planning/add.php`, `admin/top_level_planning/view.php`, `admin/top_level_planning_subjectwise.php`, `admin/top_level_planning_sections_edit.php`, `admin/top_level_planning_subject_edit.php`, `admin/weekly_planning.php`, `admin/weekly_planning_edit.php`, `admin/weekly_planning_edit_docview.php`, `admin/weekly_planning_report.php`, `admin/weekly_planning_overall_progress.php`, `admin/weekly_planning_view.php`, `admin/weekly_planning_subject_view.php`
- RBAC & users: `admin/permissions.php`, `admin/permissions_edit.php`, `admin/set_perms.php`, `admin/users_bulk_info.php`, `admin/roles/index.php`
- Academic terms: `admin/terms.php`, `admin/term_edit.php`, `admin/terms_session.php`, `admin/terms_session_edit.php`, `admin/term_weeks.php`, `admin/term_weeks_edit.php`, `admin/sections.php`, `admin/sections_edit.php`, `admin/grading_policy.php`, `admin/subject_cat.php`, `admin/subject_cat_edit.php`, `admin/subject_cat_topic.php`, `admin/subject_cat_topic_edit.php`
- HR (legacy employees): `admin/employees.php`, `admin/employees_edit.php`
- Health: `admin/health/bmi_dashboard.php`, `admin/health/bmi_records.php`, `admin/health/growth_charts.php`
- Misc admin: `admin/reports.php`, `admin/notices.php`, `admin/notices_edit.php`, `admin/fee_chalan_add.php`, `admin/students_absentees_edit.php`, `admin/student_data_verification_form.php`, `admin/student_fee_verification_form.php`, `admin/student_id_card.php`, `admin/student_id_card_new.php`, `admin/math_crossword/index.php`, `admin/hifz/sections/form.php`
- Sports (all `admin/sports/*.php` views with standard admin shell; `sports/index.php` drag-board has no legacy header)
- Class setup: `admin/class_section.php`, `admin/class_section_edit.php`, `admin/class_subjects.php`, `admin/class_subjects_edit.php`, `admin/class_sub_cat.php`, `admin/class_sub_cat_edit.php`
- QR attendance: `admin/attendance/manual.php`, `admin/attendance/report.php`, `admin/attendance/summary.php`
- Worksheets: `admin/worksheet.php`, `admin/worksheet_edit.php`, `admin/worksheet_info.php`, `admin/worksheet_info_edit.php`, `admin/worksheet_meta_info.php`, `admin/worksheet_meta_info_edit.php`
- Admissions: `admin/enroll_students.php`, `admin/admission_enquiry.php`, `admin/admission_enquiry_edit.php`
- Health (non-hostel): `admin/health/alerts.php`, `admin/health/bmi_reports.php`, `admin/health/nutrition_suggestions.php`

**Removed:** Hostel module; academy module (`a_*` controllers/views); transport module (vehicles, student vehicle assignment, transport fee types). DB cleanup: `php spark migrate` — `DropHostelModuleTables`, `DropHostelFlagColumns`, `DropAcademyTransportModuleTables`, `DropAcademyTransportFlagColumns`.

## Legacy `content-header` rollout (complete)

All admin views that used inline `<section class="content-header">` now use `<?= view('components/page_header', ...) ?>`. Shared bulk-student nav uses `components/bulk_students_header.php` (wraps `page_header`).

**Exceptions (intentional):**

- `layouts/admin_template.php` — optional `content_header` section wrapper for rare pages
- `admin/sports/index.php` — drag-board UI (no standard page header)
- `admin/students_results_card.php` — standalone print-style document inside admin shell
- Chalan/PDF/minimal print views — no admin chrome
- `frontend/dashboard/parent_section.php` — parent hub breadcrumb bar (not AdminLTE header)

Student portal quiz/date sheet views use `page_header` with `base_url('student/dashboard')` breadcrumbs.

Regenerate/tooling: `scripts/migrate_content_header.ps1` (one-off; fixes breadcrumb `url` to use `base_url()` without nested `<?=`).

## Bootstrap 5

Use Bootstrap 5 markup and `data-bs-*` attributes for new views. Legacy Bootstrap data attributes are temporarily bridged by `bootstrap5-compat.js`; prefer updating touched views to native Bootstrap 5 instead of adding new legacy markup.
