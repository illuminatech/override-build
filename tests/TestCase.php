<?php

namespace Illuminatech\OverrideBuild\Test;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Contracts\Container\Container test application instance.
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication();
    }

    /**
     * Creates dummy application instance, ensuring facades functioning.
     */
    protected function createApplication()
    {
        $this->app = Container::getInstance();

        Facade::setFacadeApplication($this->app);

        $this->app->singleton('files', function () {
            return new Filesystem;
        });
    }
}
