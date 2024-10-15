# Voucher Money

A system for managing voucher transactions with Laravel and Nova.

## Table of Contents

- [Requirements and Details](#requirements-and-details)
- [Installation](#installation)
    - [Environment Configuration](#environment-configuration)
    - [Docker Setup](#docker-setup)
    - [Running Without Docker](#running-without-docker)
- [Running the Application](#running-the-application)
- [Managing Docker](#managing-docker)
- [Troubleshooting](#troubleshooting)

## Requirements and Details

- **PHP 8.2.12** (extensions: `ext-gd`, `ext-zip`, `ext-intl`, `ext-pdo_pgsql`)
- **PostgreSQL** - v15
- **Laravel** - 11
- **Laravel Nova** - v4.35.2 (Licensed)
- **Composer** - 2.8.1

## Installation

### Install Composer Dependencies

Run the following command to install dependencies:

```shell
composer install
```

**Note**: You will need your Laravel Nova credentials during this step.

### Environment Configuration

Copy the `.env` file from `.env.docker` or `.env.production`:

```shell
cp .env.docker .env
```

Edit environment variables as needed:

```dotenv
# Set to 'local' for development, 'production' for live environments
APP_ENV=local
# Should be set to 'false' in production
APP_DEBUG=true
APP_TIMEZONE=Asia/Yerevan
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=vouchermoney
DB_USERNAME=postgres
DB_PASSWORD=123123123

NOVA_LICENSE_USERNAME=info@test.com
NOVA_LICENSE_KEY=your_license_key
```

### Docker Setup

Run Docker Compose to build and start the containers:

```shell
docker-compose up --build -d
```

This will set up all necessary services:

- **App Service** (`vouchermoney_app`):
    - Install system dependencies
    - Install necessary PHP extensions
    - Install Composer dependencies (including Nova) using credentials from `.env`
    - Set file permissions
    - Generate the application key
    - Create storage public link
    - Run database migrations and seed default data
- **Nginx Service** (`nginx_server`):
    - Configured using `./nginx/default.conf` with port defined in `.env` (`APP_PORT`)
- **PostgreSQL Service** (`postgres_db`):
    - Database, user, and password from `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
    - Port from `.env` (`DB_PORT`)
- **Redis Service** (`redis_cache`):
    - Port from `.env` (`REDIS_PORT`)

### Running Without Docker

Generate the application key:

```shell
php artisan key:generate
```

Create a storage link:

```shell
php artisan storage:link
```

Migrate and seed the database:

```shell
php artisan migrate --seed
```

## Running the Application

Access the application in your browser at:

```shell
http://localhost:8000
```

## Managing Docker

To stop the containers without terminating them:

```shell
docker-compose stop
```

To restart the containers:

```shell
docker-compose start
```

## Troubleshooting

### Database Connection Issues

Ensure PostgreSQL is running, and the credentials in `.env` are correct.

### File Permissions Issues

To fix file permission issues, run:

```shell
sudo chown -R www-data:www-data storage bootstrap/cache
```
