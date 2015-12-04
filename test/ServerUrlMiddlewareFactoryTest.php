<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Expressive\Helper\Exception\MissingHelperException;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddlewareFactory;

class ServerUrlMiddlewareFactoryTest extends TestCase
{
    public function testCreatesAndReturnsMiddlewareWhenHelperIsPresentInContainer()
    {
        $helper = $this->prophesize(ServerUrlHelper::class);
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerUrlHelper::class)->willReturn(true);
        $container->get(ServerUrlHelper::class)->willReturn($helper->reveal());

        $factory = new ServerUrlMiddlewareFactory();
        $middleware = $factory($container->reveal());
        $this->assertInstanceOf(ServerUrlMiddleware::class, $middleware);
    }

    public function testRaisesExceptionWhenContainerDoesNotContainHelper()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerUrlHelper::class)->willReturn(false);

        $factory = new ServerUrlMiddlewareFactory();

        $this->setExpectedException(MissingHelperException::class);
        $factory($container->reveal());
    }
}
