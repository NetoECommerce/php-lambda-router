<?php declare(strict_types=1);

namespace Neto\Lambda\Test\Middleware;

use Neto\Container\SimpleContainer;
use Neto\Lambda\Test\Fixture\StandaloneController;
use PHPUnit\Framework\TestCase;
use Neto\Lambda\Middleware\HandlerRouter;
use Phake;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final class HandlerRouterTest extends TestCase
{
    /** @var HandlerRouter */
    private $router;

    /** @var ServerRequestInterface */
    private $request;

    /** @var RequestHandlerInterface */
    private $handler;

    public function setUp(): void
    {
        $this->router = new HandlerRouter('standalone.bar', '\\Neto\\Lambda\\Test\\Fixture\\');
        $this->request = Phake::mock(ServerRequestInterface::class);
        $this->handler = Phake::mock(RequestHandlerInterface::class);
        Phake::when($this->handler)->handle($this->request)->thenReturn(
            Phake::mock(ResponseInterface::class)
        );
    }

    public function testConstructorSetsController()
    {
        $this->assertEquals('StandaloneController', $this->router->getController());
    }

    public function testConstructorSetsAction()
    {
        $this->assertEquals('barAction', $this->router->getAction());
    }

    public function testControllerClassIncludesNamespace()
    {
        $this->assertEquals(
            '\\Neto\\Lambda\\Test\\Fixture\\StandaloneController',
            $this->router->getControllerClass()
        );
    }

    public function testConstructorThrowsExceptionOnInvalidHandler()
    {
        $this->expectException(RuntimeException::class);
        new HandlerRouter('foobar');
    }

    public function testInstantiatingController()
    {
        $controller = $this->router->getControllerInstance();
        $this->assertInstanceOf(StandaloneController::class, $controller);
    }

    public function testInstantiatingControllerFromContainer()
    {
        $className = $this->router->getControllerClass();
        $container = Phake::mock(SimpleContainer::class);
        $controller = Phake::mock(StandaloneController::class);
        Phake::when($container)->has($className)->thenReturn(true);
        Phake::when($container)->get($className)->thenReturn($controller);
        $this->router->setContainer($container);

        $this->assertSame($controller, $this->router->getControllerInstance());
        Phake::verify($container, Phake::times(1))->get($className);
    }

    public function testInstantiatingNonExistentController()
    {
        $router = new HandlerRouter('baz.bar');
        $controller = $router->getControllerInstance();
        $this->assertNull($controller);
    }

    public function testProcessingNonExistentController()
    {
        $router = new HandlerRouter('baz.bar');
        $router->process($this->request, $this->handler);
        Phake::verify($this->handler)->handle($this->request);
    }

    public function testProcessingNonExistentAction()
    {
        $router = new HandlerRouter('standalone.baz', '\\Neto\\Lambda\\Test\\Fixture\\');
        $router->process($this->request, $this->handler);
        Phake::verify($this->handler)->handle($this->request);
    }

    public function testSuccessfulAction()
    {
        $response = $this->router->process($this->request, $this->handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        Phake::verify($this->handler, Phake::never())->handle($this->request);
    }
}