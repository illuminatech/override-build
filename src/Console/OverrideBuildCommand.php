<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Console;

use Illuminate\Console\Command;
use Illuminatech\OverrideBuild\Builder;
use Illuminatech\ArrayFactory\Facades\Factory;

class OverrideBuildCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'override-build';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Build custom assets for Nova Froala editor field';

    public function handle()
    {
        $builder = $this->createBuilder();

        $this->info('Building override...');

        $this->info('Prepare source files...');
        $builder->prepareFiles();
        $builder->overrideFiles();
        $this->info('...done.');

        $this->info('Patching files...');
        $builder->patchFiles();
        $this->info('...done.');

        $this->info('Making build...');
        $builder->build();

        $this->info('...complete.');
    }

    /**
     * @return Builder
     */
    protected function createBuilder()
    {
        $config = array_merge(
            [
                '__class' => Builder::class,
            ],
            $this->builderConfig()
        );

        return Factory::make($config);
    }

    protected function builderConfig(): array
    {
        return [];
    }
}
