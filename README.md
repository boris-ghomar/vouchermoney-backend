# Voucher Money

## Requirements and Details

- PHP - 8.2.12 (Add extension list)
  - ext-gd
  - ext-zip
  - ext-intl
  - ext-pdo_pgsql
  - ext-pgsql
- MySQL - MariaDB (10.4.32)
- Laravel 11
- Laravel Nova - v4.35.1 (Licensed)
- Composer - 2.7.7

## Installation

### Install composer dependencies
```shell
composer install
```

It will be needed to define laravel nova (nova.laravel.com) username (email address) and password (license key)

### Define environment

Copy .env file from .env.example

```shell
cp .env.example .env
```

Write environment variables

```dotenv
# Define application url
APP_URL=http://localhost:8000

# Define mysql connection credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vouchermoney
DB_USERNAME=root
DB_PASSWORD=

# Define Laravel Nova License Key
NOVA_LICENSE_KEY=
```

### Setup Application

Generate application key
```shell
php artisan key:generate
```

Create storage public link
```shell
php artisan storage:link
```

Migrate database and seed it with initial necessary data
```shell
php artisan migrate --seed
```

