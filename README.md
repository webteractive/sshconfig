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

### Option 1: Using Laravel Herd (Recommended for Mac)

[Laravel Herd](https://herd.laravel.com) automatically serves Laravel applications, so you don't need to manually start a server.

1. **Install Laravel Herd** (if not already installed):
   ```bash
   # Download from https://herd.laravel.com or use Homebrew
   brew install herd
   ```

2. **Clone the repository into Herd's sites directory:**
   ```bash
   git clone https://github.com/webteractive/sshconfig.git ~/Herd/sshconfig
   cd ~/Herd/sshconfig
   ```

   Or if you prefer to keep it elsewhere, link it to Herd:
   ```bash
   git clone https://github.com/webteractive/sshconfig.git /path/to/your/projects/sshconfig
   cd /path/to/your/projects/sshconfig
   herd link sshconfig
   ```

3. **Install dependencies and configure:**
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm run build
   ```

4. **Access the application:**
   - The app will be automatically available at `https://sshconfig.test` (or the site name you used)
   - No need to run `php artisan serve` - Herd handles it automatically!
   - Just ensure Herd is running (check the menu bar icon)

### Option 2: Manual Installation

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

### With Laravel Herd (Mac)

Once installed in Herd's directory or linked, the application is automatically served. Just visit:
- `https://sshconfig.test` (or your configured site name)

The app will be available anytime Herd is running - no manual server startup needed!

### Without Herd

Start the development server manually:

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

## Development Tips

### Laravel Herd Benefits

- **Automatic serving**: No need to run `php artisan serve`
- **HTTPS by default**: Secure local development out of the box
- **Multiple PHP versions**: Easy switching between PHP versions
- **Database management**: Built-in database tools
- **Zero configuration**: Works immediately after cloning/linking

### Switching PHP Versions (Herd)

If you need a different PHP version:
```bash
herd use php@8.3  # or php@8.2, php@8.4, etc.
```

### Viewing Logs (Herd)

```bash
herd logs sshconfig
```

## License

MIT
