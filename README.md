# SSH Config Manager

A modern web application for managing SSH configuration files. Built with Laravel and Filament.

## Features

- Visual SSH config management
- Bidirectional sync with your SSH config file
- Conflict detection and resolution
- Quick copy SSH commands
- Automatic backups before syncing

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite (default) or MySQL/PostgreSQL

## Installation

```bash
git clone https://github.com/webteractive/sshconfig.git
cd sshconfig
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

## Usage

Start the development server:

```bash
php artisan serve
```

Visit the application and set your SSH config path when prompted (default: `~/.ssh/config`).

## Development

```bash
# Run tests
php artisan test

# Format code
vendor/bin/pint
```

## Tech Stack

- Laravel 12
- Filament 4
- Livewire 3
- Pest
- SQLite

## License

MIT
