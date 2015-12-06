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
use Prophecy\Argument;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\Exception\MissingRouterException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperFactory;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\RouteResultSubjectInterface;

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

    public function testRegistersHelperAsRouteResultObserverWhenApplicationIsPresentInContainer()
    {
        $this->injectContainerService(RouterInterface::class, $this->router->reveal());

        $application = $this->prophesize(RouteResultSubjectInterface::class);
        $application->attachRouteResultObserver(Argument::type(UrlHelper::class))->shouldBeCalled();
        $this->injectContainerService(Application::class, $application->reveal());

        $helper = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(UrlHelper::class, $helper);
    }

    public function testReturnsUrlHelperEvenWhenApplicationIsNotPresentInContainer()
    {
        $this->injectContainerService(RouterInterface::class, $this->router->reveal());
        $this->container->has(Application::class)->willReturn(false);
        $helper = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(UrlHelper::class, $helper);
    }

    public function testRaisesExceptionWhenRouterIsNotPresentInContainer()
    {
        $this->setExpectedException(MissingRouterException::class);
        $this->factory->__invoke($this->container->reveal());
    }
}
