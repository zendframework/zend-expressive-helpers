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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Router\RouteResultSubjectInterface;

class UrlHelperMiddlewareTest extends TestCase
{
    public function setUp()
    {
        $this->application = $this->prophesize(RouteResultSubjectInterface::class);
        $this->helper = $this->prophesize(UrlHelper::class);
    }

    public function createMiddleware()
    {
        return new UrlHelperMiddleware(
            $this->helper->reveal(),
            $this->application->reveal()
        );
    }

    public function testInvocationRegistersHelperAsObserverOnRouteResultSubject()
    {
        $this->application
            ->attachRouteResultObserver($this->helper->reveal())
            ->shouldBeCalled();
        $request = $this->prophesize(ServerRequestInterface::class);
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
