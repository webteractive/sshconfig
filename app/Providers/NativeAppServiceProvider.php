<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Illuminate\Support\ServiceProvider;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider extends ServiceProvider implements ProvidesPhpIni
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // Only open window when running in native mode
        // NativePHP will call this method when the app starts in native mode
        try {
            Window::open()
                ->width(1200)
                ->height(800)
                ->title('SSH Config Manager')
                ->route('filament.admin.resources.ssh-configs.index');
        } catch (\Exception $e) {
            // Silently fail if not running in native mode (e.g., during web requests or CLI)
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
