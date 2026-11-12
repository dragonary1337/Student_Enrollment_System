# Student Enrollment System

SES is a Laravel application for student records, academic setup, enrollment, payment recording, and operational reports. It uses the existing Laravel Fortify authentication starter with Blade views; new registrations are pending until an administrator activates them.

## Requirements

- PHP 8.3 or later with PDO MySQL (MariaDB/MySQL) or PDO SQLite (local development and tests)
- Composer and Node.js/npm
- MariaDB 10.11 or MySQL 8, or SQLite for local development and tests

## Setup

1. Copy `.env.example` to `.env`, set the database connection/name/user/password, and generate an application key with `php artisan key:generate`.
2. Install dependencies with `composer install` and `npm install`.
3. Run `php artisan migrate` to create the relational schema and reporting views.
4. Optionally add demonstration academic records using `php artisan db:seed`.
5. Register an account through the application. Promote the initial administrator from the command line:

   ```powershell
   php artisan ses:make-admin admin@example.com
   ```

   The command requires confirmation and refuses to replace an already active administrator.
6. Build front-end assets using `npm run build`, then start the application with `php artisan serve`.

The standalone `student_enrollment_system.sql` script is for a new, empty database and includes the core schema, sample records, and reporting views. For application setup, use the Laravel migrations.

## Access and workflows

- Registration creates a pending Operator account; it cannot be used to sign in until activated by an administrator. To bootstrap the first administrator, register an account and run `php artisan ses:make-admin your-email@example.com`; after that, an active administrator can approve new users in **User accounts**. Correct credentials for a pending account show an approval message on the login page.
- Active users can manage student records, record or drop enrollments, record payments, and view reports.
- Only administrators can edit departments, programs, subjects, instructors, sections, payment methods, and account roles/statuses.
- Enrollment checks student status and section capacity. Payments are append-only and require an active enrollment for the selected term.
- Enrollment capacity checks are not serialized across simultaneous requests; this milestone build is intended for demonstration rather than concurrent production enrollment.
- Reports support student, enrollment, section-availability, and payment datasets with term/status filters. CSV exports are spreadsheet-compatible; use the browser's **Print / Save PDF** action for PDF output.

## Validation

The project test suite uses an isolated in-memory SQLite database:

```powershell
php artisan test
vendor\bin\pint --test
```

Enrollment and payment validation is covered by feature tests using SQLite. The standalone SQL script includes the core schema, sample records, and reporting views; Laravel migrations remain the supported schema path.
