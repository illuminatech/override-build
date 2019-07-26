<?php

namespace Illuminatech\OverrideBuild\Test;

use Illuminate\Support\Facades\File;
use Illuminatech\OverrideBuild\Builder;
use Illuminatech\OverrideBuild\Patches\Replace;

class BuilderTest extends TestCase
{
    /**
     * @var \Illuminatech\OverrideBuild\Builder
     */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new Builder();
        $this->builder->srcPath = __DIR__.'/_Support/source';
        $this->builder->overridePath = __DIR__.'/_Support/override';
        $this->builder->buildPath = __DIR__.'/storage/build';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        File::deleteDirectory(__DIR__.'/storage');

        parent::tearDown();
    }

    public function testPrepareFiles()
    {
        $this->builder->prepareFiles();

        $this->assertFileExists($this->builder->buildPath.'/package.json');
        $this->assertFileExists($this->builder->buildPath.'/.babelrc');
        $this->assertFileExists($this->builder->buildPath.'/resources/js/app.js');
        $this->assertFileExists($this->builder->buildPath.'/resources/js/bootstrap.js');
    }

    /**
     * @depends testPrepareFiles
     */
    public function testPrepareFilesByList()
    {
        $this->builder->srcFiles = [
            'resources',
            '.babelrc',
        ];

        $this->builder->prepareFiles();

        $this->assertFileExists($this->builder->buildPath.'/.babelrc');
        $this->assertFileExists($this->builder->buildPath.'/resources/js/app.js');

        $this->assertFileNotExists($this->builder->buildPath.'/package.json');
    }

    /**
     * @depends testPrepareFiles
     */
    public function testOverrideFiles()
    {
        $this->builder->prepareFiles();

        $this->builder->overrideFiles();

        $overriddenContent = file_get_contents($this->builder->buildPath.'/resources/js/bootstrap.js');
        $this->assertStringContainsString('override', $overriddenContent);
    }

    /**
     * @depends testPrepareFiles
     */
    public function testBuild()
    {
        $this->builder->prepareFiles();

        $this->builder->buildCommand = 'rm -f package.json';
        $this->builder->build();

        $this->assertFileNotExists($this->builder->buildPath.'/package.json');
    }

    /**
     * @depends testPrepareFiles
     */
    public function testBuildMultipleCommands()
    {
        $this->builder->prepareFiles();

        $this->builder->buildCommand = [
            'rm -f package.json',
            'rm -f .babelrc',
        ];
        $this->builder->build();

        $this->assertFileNotExists($this->builder->buildPath.'/package.json');
        $this->assertFileNotExists($this->builder->buildPath.'/.babelrc');
    }

    public function testSetupPatches()
    {
        $this->builder->setPatches([
            'file1.txt' => Replace::class,
            'file2.txt' => ['__class' => Replace::class],
            'file3.txt' => [Replace::class, Replace::class],
            'file4.txt' => [['__class' => Replace::class], ['__class' => Replace::class]],
        ]);

        $filePatches = $this->builder->getPatches();

        $this->assertTrue(isset($filePatches['file1.txt'][0]));
        $this->assertTrue($filePatches['file1.txt'][0] instanceof Replace);
        $this->assertTrue(isset($filePatches['file2.txt'][0]));
        $this->assertTrue($filePatches['file2.txt'][0] instanceof Replace);
        $this->assertTrue(isset($filePatches['file3.txt'][0]));
        $this->assertTrue($filePatches['file3.txt'][0] instanceof Replace);
        $this->assertTrue(isset($filePatches['file3.txt'][1]));
        $this->assertTrue($filePatches['file3.txt'][1] instanceof Replace);
        $this->assertTrue(isset($filePatches['file4.txt'][0]));
        $this->assertTrue($filePatches['file4.txt'][0] instanceof Replace);
        $this->assertTrue(isset($filePatches['file4.txt'][1]));
        $this->assertTrue($filePatches['file4.txt'][1] instanceof Replace);
    }

    /**
     * @depends testPrepareFiles
     * @depends testSetupPatches
     */
    public function testPatchFiles()
    {
        $this->builder->prepareFiles();

        $this->builder->setPatches([
            'resources/js/app.js' => new Replace(['replaces' => ['#app' => '#patch-replace']])
        ]);
        $this->builder->patchFiles();

        $patchedContent = file_get_contents($this->builder->buildPath.'/resources/js/app.js');
        $this->assertStringContainsString('#patch-replace', $patchedContent);
    }
}
