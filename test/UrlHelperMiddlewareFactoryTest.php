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
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\Exception\MissingHelperException;
use Zend\Expressive\Helper\Exception\MissingSubjectException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddlewareFactory;
use Zend\Expressive\Router\RouteResultSubjectInterface;

class UrlHelperMiddlewareFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function injectContainer($name, $service)
    {
        $service = $service instanceof ObjectProphecy ? $service->reveal() : $service;
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn($service);
    }

    public function testFactoryCreatesAndReturnsMiddlewareWhenHelperAndSubjectArePresentInContainer()
    {
        $helper = $this->prophesize(UrlHelper::class)->reveal();
        $subject = $this->prophesize(RouteResultSubjectInterface::class)->reveal();
        $this->injectContainer(UrlHelper::class, $helper);
        $this->injectContainer(RouteResultSubjectInterface::class, $subject);

        $factory = new UrlHelperMiddlewareFactory();
        $middleware =$factory($this->container->reveal());
        $this->assertInstanceOf(UrlHelperMiddleware::class, $middleware);
        $this->assertAttributeSame($helper, 'helper', $middleware);
        $this->assertAttributeSame($subject, 'subject', $middleware);
    }

    public function testFactoryCreatesAndReturnsMiddlewareWhenHelperAndApplicationArePresentInContainer()
    {
        $helper = $this->prophesize(UrlHelper::class)->reveal();
        $subject = $this->prophesize(RouteResultSubjectInterface::class)->reveal();
        $this->injectContainer(UrlHelper::class, $helper);
        $this->container->has(RouteResultSubjectInterface::class)->willReturn(false);
        $this->injectContainer(Application::class, $subject);

        $factory = new UrlHelperMiddlewareFactory();
        $middleware =$factory($this->container->reveal());
        $this->assertInstanceOf(UrlHelperMiddleware::class, $middleware);
        $this->assertAttributeSame($helper, 'helper', $middleware);
        $this->assertAttributeSame($subject, 'subject', $middleware);
    }

    public function testFactoryRaisesExceptionWhenContainerDoesNotContainHelper()
    {
        $this->container->has(UrlHelper::class)->willReturn(false);
        $this->injectContainer(
            RouteResultSubjectInterface::class,
            $this->prophesize(RouteResultSubjectInterface::class)
        );
        $factory = new UrlHelperMiddlewareFactory();
        $this->setExpectedException(MissingHelperException::class);
        $middleware =$factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenContainerDoesNotContainSubject()
    {
        $this->injectContainer(UrlHelper::class, $this->prophesize(UrlHelper::class));
        $this->container->has(RouteResultSubjectInterface::class)->willReturn(false);
        $this->container->has(Application::class)->willReturn(false);
        $factory = new UrlHelperMiddlewareFactory();
        $this->setExpectedException(MissingSubjectException::class);
        $middleware =$factory($this->container->reveal());
    }
}
