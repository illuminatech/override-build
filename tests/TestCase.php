<?php

namespace Illuminatech\OverrideBuild\Test;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminatech\ArrayFactory\Factory;
use Illuminatech\ArrayFactory\FactoryContract;

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

        $this->app->singleton(FactoryContract::class, Factory::class);

        $this->app->singleton('config', Repository::class);

        $this->app->singleton('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Invokes object method, even if it is private or protected.
     *
     * @param  object  $object object.
     * @param  string  $method method name.
     * @param  array  $args method arguments
     * @return mixed method result
     */
    protected function invoke($object, $method, array $args = [])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }
}
