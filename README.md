# SME Platform (Professional Modular Edition)

Production-style Employee Management System for SMEs using PHP (OOP), MySQL, Bootstrap 5, and AJAX.

## Quick start

1. Create/import database:
   - Open phpMyAdmin or MySQL client
   - Run `database.sql`
2. Update DB credentials in `config/database.php` if needed.
3. Start Apache + MySQL from XAMPP.
4. Open: `http://localhost/projectines/SME_platform/public/index.php`

## Demo accounts

- Admin: `admin@sme.local`
- Manager: `manager@sme.local`
- Employee: `employee@sme.local`
- Password for all: `Password@123`

## Professional Features Implemented

- Secure authentication with bcrypt + CSRF + session hardening
- Role-based access control (Admin, Manager, Employee)
- User management (CRUD + activate/deactivate)
- Department management
- Task management with status tracking
- Smart assignment suggestion (workload-based baseline)
- Skills management (admin skill catalog + employee proficiency map)
- Performance reviews with ratings and feedback
- Notifications API endpoint (`public/notifications.php`) for AJAX polling
- Activity logs for critical actions
- CSV report export (`public/reports.php`)
- System settings key/value management (`public/settings.php`)
- Modular folders prepared: `modules/`, `includes/`, `security/`, `assets/`, `tests/`

## Database

Run `database.sql` to create all required and advanced tables:

- users, roles
- tasks, task_comments, task_attachments
- skills, employee_skills
- performance
- notifications
- departments, user_departments
- activity_logs
- system_settings

## Debug & Testing

- Run syntax checks: `Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }`
- Run smoke test: `php tests/smoke.php`
