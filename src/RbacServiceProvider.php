<?php
namespace DigitSoft\LaravelRbac;

use DigitSoft\LaravelRbac\Commands\CreateItemMigrationsCommand;
use DigitSoft\LaravelRbac\Contracts\AccessChecker;
use DigitSoft\LaravelRbac\Contracts\Storage;
use DigitSoft\LaravelRbac\Storages\DbStorage;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Class RbacServiceProvider
 * @package DigitSoft\LaravelRbac
 */
class RbacServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application events and publish config.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/rbac.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('rbac.php');
        } else {
            $publishPath = base_path('config/rbac.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/rbac.php';
        $this->mergeConfigFrom($configPath, 'rbac');

        $this->registerClassesAliases();
        $this->registerStorage();
        $this->registerChecker();
        $this->registerManager();
        $this->registerCommands();
    }

    /**
     * Register RBAC manager
     */
    protected function registerManager()
    {
        $this->app->singleton('rbac', function ($app) {
            return new RbacManager($app['rbac.storage'], $app['rbac.checker']);
        });
    }

    /**
     * Register RBAC manager
     */
    protected function registerChecker()
    {

        $this->app->singleton('rbac.checker', function ($app) {
            /** @var Application $app */
            $checkerClass = trim(config('rbac.checker', Checkers\Basic::class), '\\');
            switch ($checkerClass) {
                case Checkers\Basic::class:
                    return $this->createAccessCheckerBasic($app);
                    break;
                case Checkers\Cached::class:
                    return $this->createAccessCheckerCached($app);
                    break;
                default:
                    return new $checkerClass();
            }
        });

        $this->app->alias('rbac.checker', AccessChecker::class);
    }

    /**
     * Register storage
     */
    protected function registerStorage()
    {
        $this->app->singleton('rbac.storage', function ($app) {
            /** @var Application $app */
            $storageClass = trim(config('rbac.storage', DbStorage::class), '\\');
            switch ($storageClass) {
                case DbStorage::class:
                    return $this->createDbStorage($app);
                    break;
                default:
                    return new $storageClass();
            }
        });

        $this->app->alias('rbac.storage', Storage::class);
    }

    /**
     * Register console commands
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.rbac.tables', function ($app) {
            return new CreateItemMigrationsCommand($app['files']);
        });

        $this->commands([
            'command.rbac.tables',
        ]);
    }

    /**
     * Register class aliases
     */
    protected function registerClassesAliases()
    {
        //$this->app->alias(Permission::class, PermissionContract::class);
        //$this->app->alias(Role::class, RoleContract::class);
    }

    /**
     * @param Application $app
     * @return DbStorage
     */
    private function createDbStorage($app)
    {
        return new DbStorage($app['db']);
    }

    /**
     * @param Application $app
     * @return AccessChecker
     */
    private function createAccessCheckerBasic($app)
    {
        return new Checkers\Basic($app['request'], $app['rbac.storage']);
    }

    /**
     * @param Application $app
     * @return AccessChecker
     */
    private function createAccessCheckerCached($app)
    {
        return new Checkers\Cached($app['request'], $app['rbac.storage']);
    }

    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [
            'rbac',
            'rbac.storage',
            'rbac.checker',
            'command.rbac.tables',
            'command.rbac.storage.files',
        ];
    }
}
