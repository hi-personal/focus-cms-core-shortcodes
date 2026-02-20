<?php

namespace Modules\FocusCmsCoreShortcodes\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

use App\Services\ShortcodeRegistry;

use Modules\FocusCmsCoreShortcodes\Classes\Shortcodes\HtmlShortcode;
use Modules\FocusCmsCoreShortcodes\Classes\Shortcodes\CodeShortcode;
use Modules\FocusCmsCoreShortcodes\Classes\Shortcodes\ImageShortcode;
use Modules\FocusCmsCoreShortcodes\Classes\Shortcodes\GalleryShortcode;
use Modules\FocusCmsCoreShortcodes\Classes\Shortcodes\FileShortcode;
use Modules\FocusCmsCoreShortcodes\Classes\Shortcodes\WidgetShortcode;

class ModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'FocusCmsCoreShortcodes';

    protected string $moduleNameLower = 'focuscmscoreshortcodes';


    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        $this->registerConfig();
    }


    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    public function boot(): void
    {
        if (!Schema::hasTable('options')) {
            return;
        }

        $this->registerViews();
        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerCommands();
        $this->registerBladeComponents();
        $this->registerShortcodes();
    }


    /*
    |--------------------------------------------------------------------------
    | SHORTCODES
    |--------------------------------------------------------------------------
    */

    protected function registerShortcodes(): void
    {
        $registry = app(ShortcodeRegistry::class);

        /*
         * FONTOS: sorrend számít
         * CodeShortcode előbb fusson, hogy a többi shortcode ne fusson code blockon belül
         */

        $registry->register(new CodeShortcode());

        $registry->register(new HtmlShortcode());

        $registry->register(new ImageShortcode());

        $registry->register(new GalleryShortcode());

        $registry->register(new FileShortcode());

        $registry->register(new WidgetShortcode());
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    protected function registerConfig(): void
    {
        $path = base_path("Modules/{$this->moduleName}/config");

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path.'/*.php') as $file) {

            $key = pathinfo($file, PATHINFO_FILENAME);

            $this->mergeConfigFrom(
                $file,
                "module.{$this->moduleNameLower}.{$key}"
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VIEWS
    |--------------------------------------------------------------------------
    */

    protected function registerViews(): void
    {
        $path = base_path("Modules/{$this->moduleName}/resources/views");

        if (!is_dir($path)) {
            return;
        }

        $this->loadViewsFrom($path, $this->moduleNameLower);

        View::addNamespace($this->moduleNameLower, $path);
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSLATIONS
    |--------------------------------------------------------------------------
    */

    protected function registerTranslations(): void
    {
        $path = base_path("Modules/{$this->moduleName}/resources/lang");

        if (!is_dir($path)) {
            return;
        }

        $this->loadTranslationsFrom($path, $this->moduleNameLower);

        Lang::addNamespace($this->moduleNameLower, $path);
    }


    /*
    |--------------------------------------------------------------------------
    | ROUTES
    |--------------------------------------------------------------------------
    */

    protected function registerRoutes(): void
    {
        $web = base_path("Modules/{$this->moduleName}/routes/web.php");

        if (!file_exists($web)) {
            return;
        }

        Route::middleware(['web'])->group($web);
    }


    /*
    |--------------------------------------------------------------------------
    | MIGRATIONS
    |--------------------------------------------------------------------------
    */

    protected function registerMigrations(): void
    {
        $path = base_path("Modules/{$this->moduleName}/database/migrations");

        if (!is_dir($path)) {
            return;
        }

        $this->loadMigrationsFrom($path);
    }


    /*
    |--------------------------------------------------------------------------
    | COMMANDS
    |--------------------------------------------------------------------------
    */

    protected function registerCommands(): void
    {
        $path = base_path("Modules/{$this->moduleName}/Console/Commands");

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path.'/*.php') as $file) {

            $class = "Modules\\{$this->moduleName}\\Console\\Commands\\".basename($file, '.php');

            if (class_exists($class)) {
                $this->commands($class);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BLADE COMPONENTS
    |--------------------------------------------------------------------------
    */

    protected function registerBladeComponents(): void
    {
        $path = base_path("Modules/{$this->moduleName}/Classes/Components");

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path.'/*.php') as $file) {

            $class = "Modules\\{$this->moduleName}\\Classes\\Components\\".basename($file, '.php');

            if (!class_exists($class)) {
                continue;
            }

            $tag = strtolower(
                preg_replace('/(?<!^)[A-Z]/', '-$0', basename($file, '.php'))
            );

            Blade::component($class, "{$this->moduleNameLower}-{$tag}");
        }
    }
}