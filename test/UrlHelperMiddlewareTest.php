<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Router\RouteResult;

class UrlHelperMiddlewareTest extends TestCase
{
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
        $response = $this->prophesize(ResponseInterface::class);
        $next = function ($req, $res) {
            return 'COMPLETE';
        };
        $middleware = $this->createMiddleware();
        $this->assertEquals('COMPLETE', $middleware(
            $request->reveal(),
            $response->reveal(),
            $next
        ));
    }

    public function testInvocationDoesNotInjectHelperWithRouteResultWhenAbsentInRequest()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn(false);
        $this->helper->setRouteResult(Argument::any())->shouldNotBeCalled();
        $response = $this->prophesize(ResponseInterface::class);
        $next = function ($req, $res) {
            return 'COMPLETE';
        };
        $middleware = $this->createMiddleware();
        $this->assertEquals('COMPLETE', $middleware(
            $request->reveal(),
            $response->reveal(),
            $next
        ));
    }
}
