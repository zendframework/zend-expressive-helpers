<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Expressive\Helper\Exception\MissingRouterException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperFactory;
use Zend\Expressive\Router\RouterInterface;

class UrlHelperFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->factory = new UrlHelperFactory();
    }

    public function injectContainerService($name, $service)
    {
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn($service);
    }

    public function testFactoryReturnsHelperWithRouterInjected()
    {
        $this->injectContainerService(RouterInterface::class, $this->router->reveal());

        $helper = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(UrlHelper::class, $helper);
        $this->assertAttributeSame($this->router->reveal(), 'router', $helper);
    }

    public function testFactoryRaisesExceptionWhenRouterIsNotPresentInContainer()
    {
        $this->setExpectedException(MissingRouterException::class);
        $this->factory->__invoke($this->container->reveal());
    }
}
