<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminatech\OverrideBuild\Builder;
use Illuminate\Console\ConfirmableTrait;
use Illuminatech\ArrayFactory\Facades\Factory;

/**
 * OverrideBuildCommand re-builds materials from 3rd party libraries with patch.
 *
 * @see \Illuminatech\OverrideBuild\Builder
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class OverrideBuildCommand extends Command
{
    use ConfirmableTrait;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'override-build {package}
                    {--force : Force the operation to run even if existing up-to-date build is detected}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Re-builds materials from 3rd party libraries with patch.';

    /**
     * Builds specified package.
     */
    public function handle()
    {
        $package = $this->argument('package');

        $builder = $this->createBuilder($package);

        $confirmCallback = function () use ($builder) {
            return $builder->isBuildActual();
        };
        if (! $this->confirmToProceed('Build for "'.$package.'" is already up-to-date. Do you wish to overwrite it?', $confirmCallback)) {
            return;
        }

        $this->info('Building "'.$package.'"...');

        $this->info('Prepare source files...');
        $builder->prepareFiles();
        $builder->overrideFiles();
        $this->info('...done.');

        $this->info('Patching files...');
        $builder->patchFiles();
        $this->info('...done.');

        $this->info('Making build...');
        $builder->build();

        $this->info('Cleaning up...');
        $builder->cleanupFiles();
        $this->info('...done.');

        $this->info('...complete.');
    }

    /**
     * Creates builder for the specified package name.
     *
     * @param  string  $package package name.
     * @return Builder package builder instance.
     */
    protected function createBuilder(string $package)
    {
        $packagesConfig = $this->packagesConfig();

        if (! isset($packagesConfig[$package])) {
            throw new \InvalidArgumentException("Package '{$package}' is undefined.");
        }

        $config = array_merge(
            [
                '__class' => Builder::class,
            ],
            $packagesConfig[$package]
        );

        return Factory::make($config);
    }

    /**
     * @return array configuration for available packages.
     */
    protected function packagesConfig(): array
    {
        return Config::get('override-build.packages', []);
    }
}
