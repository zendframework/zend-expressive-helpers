<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Helper\Template;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Helper\Template\RouteTemplateVariableMiddleware;
use Zend\Expressive\Helper\Template\TemplateVariableContainer;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;

class RouteTemplateVariableMiddlewareTest extends TestCase
{
    public function setUp()
    {
        $this->request    = $this->prophesize(ServerRequestInterface::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
        $this->handler    = $this->prophesize(RequestHandlerInterface::class);
        $this->container  = new TemplateVariableContainer();
        $this->middleware = new RouteTemplateVariableMiddleware();
    }

    public function testMiddlewareIsANoOpIfNoVariableContainerPresent()
    {
        $this->request
            ->getAttribute(TemplateVariableContainer::class)
            ->willReturn(null)
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(RouteResult::class, null)
            ->shouldNotBeCalled();

        $this->handler
            ->handle(Argument::that([$this->request, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function testMiddlewareWillInjectNullValueForRouteIfNoRouteResultInRequest()
    {
        $this->request
            ->getAttribute(TemplateVariableContainer::class)
            ->willReturn($this->container)
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(RouteResult::class, null)
            ->willReturn(null)
            ->shouldBeCalledTimes(1);

        $originalContainer = $this->container;
        $this->request
            ->withAttribute(
                TemplateVariableContainer::class,
                Argument::that(function ($container) use ($originalContainer) {
                    TestCase::assertNotSame($container, $originalContainer);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertNull($container->get('route'));
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);

        $this->handler
            ->handle(Argument::that([$this->request, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function routeTypes() : iterable
    {
        yield 'null'  => [null];
        yield 'false' => [false];

        $route = $this->prophesize(Route::class)->reveal();
        yield 'route' => [$route];
    }

    /**
     * @dataProvider routeTypes
     * @param null|false|Route $route
     */
    public function testMiddlewareWillInjectRoutePulledFromRequestRouteResult($route)
    {
        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult
            ->getMatchedRoute()
            ->willReturn($route)
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(TemplateVariableContainer::class)
            ->willReturn($this->container)
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(RouteResult::class, null)
            ->will([$routeResult, 'reveal'])
            ->shouldBeCalledTimes(1);

        $originalContainer = $this->container;
        $this->request
            ->withAttribute(
                TemplateVariableContainer::class,
                Argument::that(function ($container) use ($originalContainer, $route) {
                    TestCase::assertNotSame($container, $originalContainer);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertSame($container->get('route'), $route);
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);

        $this->handler
            ->handle(Argument::that([$this->request, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }
}
