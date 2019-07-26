<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild;

use Illuminate\Support\ServiceProvider;

/**
 * OverrideBuildServiceProvider bootstraps 'override-build' command to Laravel console.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class OverrideBuildServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->registerPublications();

        $this->commands([
            Console\OverrideBuildCommand::class,
        ]);
    }

    /**
     * Register resources to be published by the publish command.
     */
    protected function registerPublications(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/override-build.php' => $this->app->make('path.config').DIRECTORY_SEPARATOR.'override-build.php',
        ], 'config');
    }
}
