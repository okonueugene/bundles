# Laravel Application

A reusable open-source Laravel application foundation with server-rendered
pages, database-backed workflows, external service adapters, administrative
tools, and automated tests. It is designed to be adapted, extended, and
self-hosted.

## Features

- Laravel routing, controllers, models, migrations, and validation
- Blade templates with Tailwind CSS and Alpine.js
- Database-backed workflows with status and retry handling
- Configurable integrations through service adapters
- Administrative views for reviewing and managing records
- Feature and unit tests

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- MySQL, SQLite, or another Laravel-supported database

## Installation

```bash
git clone <repository-url>
cd <repository-directory>
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Configure the database and any optional integrations in `.env` before starting
the application.

## Local development

Start the application:

```bash
php artisan serve
```

Compile frontend assets during development:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

## Testing

```bash
php artisan test
```

## Scheduled tasks

If scheduled tasks are enabled, configure the host to run the scheduler every
minute:

```bash
php artisan schedule:run
```

Review the application configuration before enabling queues, scheduled tasks,
or external integrations in a production environment.

## Contributing

1. Create a topic branch.
2. Make focused changes and add tests where appropriate.
3. Run the relevant tests and asset build.
4. Open a pull request describing the change and its verification.

Do not commit credentials, generated archives, local reports, runtime logs, or
environment-specific configuration.

## License

This project is released under the MIT License. See `LICENSE` if included.
