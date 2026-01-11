# SSH Config Manager

A modern desktop application built with Laravel and Filament for managing SSH configuration files. Easily create, edit, duplicate, and sync SSH configurations with a beautiful, intuitive interface.

## Download

Download the latest macOS installer from the [Releases](https://github.com/webteractive/sshconfig/releases) page.

### Which file should I download?

- **macOS Apple Silicon (M1/M2/M3)**: Download `SSHConfig-x.x.x-arm64.dmg`
- **macOS Intel**: Download `SSHConfig-x.x.x-x64.dmg`

**Not sure which one?** Check your Mac's processor:
- Apple menu → About This Mac → Chip: If it says "Apple" or "M1/M2/M3", download the **arm64** version
- If it says "Intel", download the **x64** version

### Installation Instructions

1. **Download** the appropriate .dmg file for your Mac architecture
2. **Open** the downloaded .dmg file (double-click it)
3. **Drag** SSH Config Manager to your Applications folder
4. **Launch** the app from Applications
5. **Set SSH Config Path** when prompted (typically `~/.ssh/config`)

### First Launch

On first launch, you'll be prompted to set the path to your SSH config file. This is required for the application to sync configurations.

**Default path**: `~/.ssh/config`

### System Requirements

- macOS 10.12 or later
- SSH config file access

## Features

- **Visual SSH Config Management**: Create, edit, and delete SSH configurations through an elegant interface
- **Bidirectional Sync**: Sync configurations between your SSH config file and the database
- **Conflict Detection**: Automatically detect and resolve conflicts when syncing
- **Quick Copy**: Copy SSH commands directly from the interface with one click
- **Duplicate Configurations**: Quickly duplicate existing SSH configs with unique hostnames
- **Automatic Backups**: Creates backups before syncing to protect your existing configurations
- **Desktop Application**: Available as a native macOS desktop app built with NativePHP

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js and npm
- SQLite (default) or MySQL/PostgreSQL

## Installation

1. Clone the repository:
```bash
git clone https://github.com/webteractive/sshconfig.git
cd sshconfig
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Build frontend assets:
```bash
npm run build
```

## Usage

### Desktop Application (macOS)

See the [Download](#download) section above for detailed installation instructions.

### Web Application (Development)

Start the development server:
```bash
php artisan serve
```

Visit the application in your browser and set your SSH config path when prompted.

### Building from Source

To build the desktop application yourself:
```bash
php artisan native:build mac
```

The `.dmg` files will be created in the `nativephp/electron/dist/` directory (both arm64 and x64 versions).

For development:
```bash
composer run native:dev
```

## Key Features Explained

### Setting SSH Config Path

On first launch, you'll be prompted to set the path to your SSH config file (typically `~/.ssh/config`). This path is stored and used for all sync operations.

### Syncing Configurations

- **Sync From File**: Reads your SSH config file and updates the database
- **Sync To File**: Writes all database configurations to your SSH config file
- **Sync Both**: Bidirectionally syncs, detecting and reporting conflicts

### Managing Configurations

- **Create**: Add new SSH configurations with host, hostname, user, port, and identity file
- **Edit**: Update existing configurations
- **Duplicate**: Create copies of configurations with unique hostnames
- **Delete**: Remove configurations (with automatic sync to file)

### SSH Command

Each configuration displays a copyable SSH command (e.g., `ssh hostname`) that you can quickly copy and paste into your terminal.

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

This project uses Laravel Pint for code formatting:

```bash
vendor/bin/pint
```

### Project Structure

- `app/Actions/` - Business logic actions for SSH config operations
- `app/Filament/Actions/` - Filament UI actions
- `app/Filament/Resources/` - Filament resources and pages
- `app/Models/` - Eloquent models
- `tests/` - Pest test suite

## Tech Stack

- **Laravel 12** - PHP framework
- **Filament 4** - Admin panel framework
- **Livewire 3** - Dynamic UI components
- **NativePHP** - Desktop application framework
- **Pest** - Testing framework
- **SQLite** - Database (default)

## Building Releases

To create a release:

```bash
./bin/release <version>
```

Example:
```bash
./bin/release 1.0.0
```

This will:
1. Update the version in `config/nativephp.php`
2. Build the macOS `.dmg` file
3. Create a Git tag
4. Create a GitHub release with the `.dmg` attached

## License

The SSH Config Manager is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
