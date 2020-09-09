<?php

namespace Illuminatech\OverrideBuild\Test\Console;

use Illuminate\Support\Facades\Config;
use Illuminatech\OverrideBuild\Builder;
use Illuminatech\OverrideBuild\Console\OverrideBuildCommand;
use Illuminatech\OverrideBuild\Test\TestCase;

class OverrideBuildCommandTest extends TestCase
{
    public function testCreateBuilder()
    {
        $command = new OverrideBuildCommand();

        Config::set('override-build.packages', [
            'test-package' => [
                'srcPath' => '/test/src/path',
            ],
        ]);

        /** @var $builder Builder */
        $builder = $this->invoke($command, 'createBuilder', ['test-package']);

        $this->assertTrue($builder instanceof Builder);
        $this->assertSame('/test/src/path', $builder->srcPath);

        $this->expectException(\InvalidArgumentException::class);
        $this->invoke($command, 'createBuilder', ['undefined-package']);
    }
}
