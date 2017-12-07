<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Router\RouteResult;

class UrlHelperMiddlewareTest extends TestCase
{
    /**
     * @var UrlHelper|ObjectProphecy
     */
    private $helper;

    public function setUp()
    {
        $this->helper = $this->prophesize(UrlHelper::class);
    }

    public function createMiddleware()
    {
        return new UrlHelperMiddleware($this->helper->reveal());
    }

    public function testInvocationInjectsHelperWithRouteResultWhenPresentInRequest()
    {
        $routeResult = $this->prophesize(RouteResult::class)->reveal();
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);
        $this->helper->setRouteResult($routeResult)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(RequestInterface::class))->will(function ($req) use ($response) {
            return $response;
        });

        $middleware = $this->createMiddleware();
        $this->assertSame($response, $middleware->process(
            $request->reveal(),
            $handler->reveal()
        ));
    }

    public function testInvocationDoesNotInjectHelperWithRouteResultWhenAbsentInRequest()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn(false);
        $this->helper->setRouteResult(Argument::any())->shouldNotBeCalled();

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(RequestInterface::class))->will(function ($req) use ($response) {
            return $response;
        });

        $middleware = $this->createMiddleware();
        $this->assertSame($response, $middleware->process(
            $request->reveal(),
            $handler->reveal()
        ));
    }
}
