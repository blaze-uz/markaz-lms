# Markaz LMS

Markaz LMS is a free, open-source learning management system for training centres. It manages students, teachers, groups, lessons and the centre's finances with Laravel, Livewire, Blade and selected Inertia/Vue pages.

## Features

- Students, teachers, staff, branches, courses and classroom schedules
- Attendance, homework, exams and quizzes
- Payments, debts, refunds, expenses, staff salaries and financial reports
- Leads, boards, calls and reminders
- SMS templates, single/bulk/scheduled messages, debt, birthday and attendance notifications, and delivery reports
- Telegram bots and student/staff web apps
- Roles and permissions, products and orders

## Requirements

PHP 8.3 with PostgreSQL PDO, mbstring, XML, cURL, ZIP, GD and BCMath extensions; Composer; PostgreSQL; Node.js 22 and npm. Redis is optional for Horizon and Redis queues. Writable `storage` and `bootstrap/cache` directories are required.

## Installation

Clone this repository and enter `markaz-lms`. Create an empty PostgreSQL database named `markaz_lms`, then run:

```sh
composer install
cp .env.example .env
```

Set `APP_URL` and your database credentials in `.env`. The defaults use PostgreSQL on `127.0.0.1:5432`, user `postgres`, database `markaz_lms`, file cache/sessions, the sync queue and the log SMS driver.

```sh
php artisan key:generate
php artisan migrate --seed
php artisan lms:install admin@example.com 'choose-a-strong-password'
npm ci
npm run build
php artisan serve
```

The install command creates the admin, main branch, settings and default templates, and links public storage. Configure the centre's name, contact details and notification templates in Settings. Point your web server's document root at `public`.

For tests, create `markaz_lms_test`, migrate it, and run:

```sh
DB_DATABASE=markaz_lms_test php artisan migrate --seed
DB_DATABASE=markaz_lms_test php vendor/bin/phpunit
```

## Scheduler and queue

Install the normal Laravel cron entry, replacing the project path:

```cron
* * * * * cd /path/to/markaz-lms && php artisan schedule:run >> /dev/null 2>&1
```

`QUEUE_CONNECTION=sync` needs no worker. For background processing and delayed retries, set `QUEUE_CONNECTION=database` and run a supervised worker:

```sh
php artisan queue:work --queue=sms,default --tries=3
```

The jobs table is included in the migrations. Horizon requires Redis; set `HORIZON_ALLOWED_EMAILS` to a comma-separated list of permitted account emails.

## SMS drivers

`SMS_DRIVER=log` is the default. It sends no network requests, logs message content in the Laravel log and marks messages sent. Template moderation is approved immediately. Enable SMS and choose notification templates in Settings.

To send through the centre's own Eskiz.uz account:

```dotenv
SMS_DRIVER=eskiz
ESKIZ_BASE_URL=https://notify.eskiz.uz
ESKIZ_EMAIL=
ESKIZ_PASSWORD=
ESKIZ_FROM=4546
ESKIZ_CALLBACK_TOKEN=
```

Set the email/password and a long random callback token. With `APP_URL=https://lms.example.com`, the callback URL to give Eskiz is:

```text
https://lms.example.com/api/sms/eskiz/callback?token=YOUR_CALLBACK_TOKEN
```

The driver also supplies this URL on single and batch requests. An unset callback token returns 503; an incorrect token returns 401. Submit templates to moderation and check their status from the templates page. Eskiz approvals belong to your own account; default templates need moderation when using this driver. Deleting a template only removes the local record.

## Theme (Velzon)

The paid Velzon theme is excluded. The original Blade pages retain their existing classes and render without theme styling until you copy the assets from your own Velzon v3.0.0 Laravel licence. Copied theme assets are ignored by Git and are not covered by this project's MIT licence.

Copy these files from the Velzon v3.0.0 Laravel package (paths checked against the package this project was originally built on):

| Velzon package file | Copy to |
| --- | --- |
| `public/build/css/bootstrap.min.css` | `public/assets/css/bootstrap.min.css` |
| `public/build/css/icons.min.css` | `public/assets/css/icons.min.css` |
| `public/build/css/app.min.css` | `public/assets/css/app.min.css` |
| `public/build/fonts/*` | `public/assets/fonts/` |
| `public/build/js/layout.js` | `public/assets/js/layout.js` |
| `public/build/js/app.js` | `public/assets/js/theme.js` |
| `public/build/js/pages/nestable.init.js` | `public/assets/js/nestable.init.js` (menu settings page) |
| `public/build/js/pages/profile-setting.init.js` | `public/assets/js/profile-setting.init.js` (profile page) |

The old system ran a lightly modified `app.js` (layout state kept in `sessionStorage`, collapsed menus kept open on small screens); the stock file works the same apart from that.

`public/assets/css/custom.min.css` is application CSS and stays. Layout dependencies such as Bootstrap's JavaScript, Simplebar, Waves, Feather, Choices, jQuery, ApexCharts, Toastify, Popper and Tippy are retained as open-source libraries. The Vue/Tailwind build uses the repository's own sources and does not compile the licensed theme.

## License

[MIT](LICENSE), copyright 2023–2026 Markaz LMS contributors. Bundled open-source libraries retain their own licences.

## O'zbekcha

Markaz LMS — o'quv markazlari uchun bepul va ochiq kodli boshqaruv tizimi. PHP 8.3, Composer, PostgreSQL va Node.js 22 kerak. PostgreSQL'da `markaz_lms` bazasini yarating, loyiha papkasida:

```sh
composer install
cp .env.example .env
# .env ichida APP_URL va PostgreSQL ulanishini sozlang.
php artisan key:generate
php artisan migrate --seed
php artisan lms:install admin@example.com 'kuchli-parol-tanlang'
npm ci
npm run build
php artisan serve
```

Standart sozlamalar Redis talab qilmaydi: navbat `sync`, kesh va sessiya `file`, SMS `log`. Haqiqiy SMS uchun yuqoridagi Eskiz sozlamalarini kiriting. Cron uchun yuqoridagi `schedule:run` qatorini qo'shing; fon navbati uchun `QUEUE_CONNECTION=database` va `queue:work --queue=sms,default --tries=3` ishlating. Sahifalarni bezash uchun yuqoridagi mavzu bo'limiga ko'ra o'z litsenziyangizdagi fayllarni ko'chiring. Loyiha MIT litsenziyasida.
