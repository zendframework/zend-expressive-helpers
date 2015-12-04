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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\ServerUrlMiddleware;

class ServerUrlMiddlewareTest extends TestCase
{
    public function testMiddlewareInjectsHelperWithUri()
    {
        $uri = $this->prophesize(UriInterface::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getUri()->willReturn($uri->reveal());
        $response = $this->prophesize(ResponseInterface::class);

        $helper = new ServerUrlHelper();
        $middleware = new ServerUrlMiddleware($helper);

        $invoked = false;
        $next = function ($req, $res) use (&$invoked) {
            $invoked = true;
            return $res;
        };

        $test = $middleware($request->reveal(), $response->reveal(), $next);
        $this->assertSame($response->reveal(), $test, 'Unexpected return value from middleware');
        $this->assertTrue($invoked, 'next() was not invoked');

        $this->assertAttributeSame(
            $uri->reveal(),
            'uri',
            $helper,
            'Helper was not injected with URI from request'
        );
    }
}
