<?php declare(strict_types=1);

namespace Neto\Lambda\Test\Controller;

use Neto\Container\SimpleContainer;
use Neto\Lambda\Test\Fixture\ContainerAwareController;
use Phake;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class AbstractControllerTest extends TestCase
{
    /** @var ContainerAwareController */
    private $controller;

    /** @var SimpleContainer */
    private $container;

    public function setUp(): void
    {
        $this->controller = new ContainerAwareController();
        $this->container = new SimpleContainer();
    }

    public function testGetDefaultWithoutContainerSet()
    {
        $this->assertEquals('bar', $this->controller->getContainerKeyAction());
    }

    public function testGetDefaultWithContainerSet()
    {
        $this->controller->setContainer($this->container);
        $this->assertEquals('bar', $this->controller->getContainerKeyAction());
    }

    public function testGetValueFromContainer()
    {
        $this->container->set('foo', 'baz');
        $this->controller->setContainer($this->container);
        $this->assertEquals('baz', $this->controller->getContainerKeyAction());
    }

    public function testLogDoesNotThrowIfNoLoggerSet()
    {
        try {
            $this->controller->logAction();
        } catch (\Exception $e) {
            $this->fail('log() method should not throw an exception');
        }

        // if we got this far, the test passed
        $this->assertTrue(true);
    }

    public function testLogInvokesLoggerFromContainer()
    {
        $logger = Phake::mock(LoggerInterface::class);
        $this->container->set(LoggerInterface::class, $logger);
        $this->controller->setContainer($this->container);

        $this->controller->logAction();

        Phake::verify($logger, Phake::times(1))->log(LogLevel::ERROR, 'Danger to manifold', []);
    }
}
