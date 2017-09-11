<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;
use Zend\Expressive\Helper\Exception\RuntimeException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Exception\RuntimeException as RouterException;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

class UrlHelperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var RouterInterface|ObjectProphecy
     */
    private $router;

    public function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function createHelper()
    {
        return new UrlHelper($this->router->reveal());
    }

    public function testRaisesExceptionOnInvocationIfNoRouteProvidedAndNoResultPresent()
    {
        $helper = $this->createHelper();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('use matched result');
        $helper();
    }

    public function testRaisesExceptionOnInvocationIfNoRouteProvidedAndResultIndicatesFailure()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(true);
        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('routing failed');
        $helper();
    }

    public function testRaisesExceptionOnInvocationIfRouterCannotGenerateUriForRouteProvided()
    {
        $this->router->generateUri('foo', [], [])->willThrow(RouterException::class);
        $helper = $this->createHelper();

        $this->expectException(RouterException::class);
        $helper('foo');
    }

    public function testWhenNoRouteProvidedTheHelperUsesComposedResultToGenerateUrl()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('foo');
        $result->getMatchedParams()->willReturn(['bar' => 'baz']);

        $this->router->generateUri('foo', ['bar' => 'baz'], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper());
    }

    public function testWhenNoRouteProvidedTheHelperMergesPassedParametersWithResultParametersToGenerateUrl()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('foo');
        $result->getMatchedParams()->willReturn(['bar' => 'baz']);

        $this->router->generateUri('foo', ['bar' => 'baz', 'baz' => 'bat'], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper(null, ['baz' => 'bat']));
    }

    public function testWhenRouteProvidedTheHelperDelegatesToTheRouterToGenerateUrl()
    {
        $this->router->generateUri('foo', ['bar' => 'baz'], [])->willReturn('URL');
        $helper = $this->createHelper();
        $this->assertEquals('URL', $helper('foo', ['bar' => 'baz']));
    }

    public function testIfRouteResultRouteNameDoesNotMatchRequestedNameItWillNotMergeParamsToGenerateUri()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('not-resource');
        $result->getMatchedParams()->shouldNotBeCalled();

        $this->router->generateUri('resource', [], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper('resource'));
    }

    public function testMergesRouteResultParamsWithProvidedParametersToGenerateUri()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('resource');
        $result->getMatchedParams()->willReturn(['id' => 1]);

        $this->router->generateUri('resource', ['id' => 1, 'version' => 2], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper('resource', ['version' => 2]));
    }

    public function testProvidedParametersOverrideAnyPresentInARouteResultWhenGeneratingUri()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('resource');
        $result->getMatchedParams()->willReturn(['id' => 1]);

        $this->router->generateUri('resource', ['id' => 2], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper('resource', ['id' => 2]));
    }

    public function testWillNotReuseRouteResultParamsIfReuseResultParamsFlagIsFalseWhenGeneratingUri()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('resource');
        $result->getMatchedParams()->willReturn(['id' => 1]);

        $this->router->generateUri('resource', [], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper('resource', [], [], null, ['reuse_result_params' => false]));
    }

    public function testCanInjectRouteResult()
    {
        $result = $this->prophesize(RouteResult::class);
        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());
        $this->assertAttributeSame($result->reveal(), 'result', $helper);
    }

    public function testAllowsSettingBasePath()
    {
        $helper = $this->createHelper();
        $helper->setBasePath('/foo');
        $this->assertAttributeEquals('/foo', 'basePath', $helper);
    }

    public function testSlashIsPrependedWhenBasePathDoesNotHaveOne()
    {
        $helper = $this->createHelper();
        $helper->setBasePath('foo');
        $this->assertAttributeEquals('/foo', 'basePath', $helper);
    }

    public function testBasePathIsPrependedToGeneratedPath()
    {
        $this->router->generateUri('foo', ['bar' => 'baz'], [])->willReturn('/foo/baz');
        $helper = $this->createHelper();
        $helper->setBasePath('/prefix');
        $this->assertEquals('/prefix/foo/baz', $helper('foo', ['bar' => 'baz']));
    }

    public function testBasePathIsPrependedToGeneratedPathWhenUsingRouteResult()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('foo');
        $result->getMatchedParams()->willReturn(['bar' => 'baz']);

        $this->router->generateUri('foo', ['bar' => 'baz'], [])->willReturn('/foo/baz');

        $helper = $this->createHelper();
        $helper->setBasePath('/prefix');
        $helper->setRouteResult($result->reveal());

        // test with explicit params
        $this->assertEquals('/prefix/foo/baz', $helper(null, ['bar' => 'baz']));

        // test with implicit route result params
        $this->assertEquals('/prefix/foo/baz', $helper());
    }

    public function testGenerateProxiesToInvokeMethod()
    {
        $routeName = 'foo';
        $routeParams = ['bar'];
        $queryParams = ['foo' => 'bar'];
        $fragmentIdentifier = 'foobar';
        $options = ['router' => ['foobar' => 'baz'], 'reuse_result_params' => false];

        $helper = Mockery::mock(UrlHelper::class)->shouldDeferMissing();
        $helper->shouldReceive('__invoke')
            ->once()
            ->with($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options)
            ->andReturn('it worked');

        $this->assertSame(
            'it worked',
            $helper->generate($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options)
        );
    }

    public function invalidBasePathProvider()
    {
        return [
            [new stdClass('foo')],
            [['bar']],
        ];
    }

    /**
     * @dataProvider invalidBasePathProvider
     *
     * @param mixed $basePath
     */
    public function testThrowsExceptionWhenSettingInvalidBasePaths($basePath)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Base path must be a string; received [a-zA-Z]+/');

        $helper = $this->createHelper();
        $helper->setBasePath($basePath);
    }

    public function testIfRouteResultIsFailureItWillNotMergeParamsToGenerateUri()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(true);
        $result->getMatchedRouteName()->willReturn('resource');
        $result->getMatchedParams()->shouldNotBeCalled();

        $this->router->generateUri('resource', [], [])->willReturn('URL');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals('URL', $helper('resource'));
    }

    public function testOptionsArePassedToRouter()
    {
        $this->router->generateUri('foo', [], ['bar' => 'baz'])->willReturn('URL');
        $helper = $this->createHelper();
        $this->assertEquals('URL', $helper('foo', [], [], null, ['router' => ['bar' => 'baz']]));
    }

    public function queryParametersAndFragmentProvider()
    {
        return [
            'none'           => [[], null, ''],
            'query'          => [['qux' => 'quux'], null, '?qux=quux'],
            'fragment'       => [[], 'corge', '#corge'],
            'query+fragment' => [['qux' => 'quux'], 'cor-ge', '?qux=quux#cor-ge'],
        ];
    }

    /**
     * @dataProvider queryParametersAndFragmentProvider
     *
     * @param array $queryParams
     * @param null|string $fragmentIdentifier
     * @param string $expected
     */
    public function testQueryParametersAndFragment(array $queryParams, $fragmentIdentifier, $expected)
    {
        $this->router->generateUri('foo', ['bar' => 'baz'], [])->willReturn('/foo/baz');
        $helper = $this->createHelper();

        $this->assertEquals(
            '/foo/baz' . $expected,
            $helper('foo', ['bar' => 'baz'], $queryParams, $fragmentIdentifier)
        );
    }

    public function invalidFragmentProvider()
    {
        return [
            [''],
            ['#'],
        ];
    }

    /**
     * @dataProvider invalidFragmentProvider
     *
     * @param string $fragmentIdentifier
     */
    public function testRejectsInvalidFragmentIdentifier($fragmentIdentifier)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fragment identifier must conform to RFC 3986');
        $this->expectExceptionCode(400);

        $helper = $this->createHelper();
        $helper('foo', [], [], $fragmentIdentifier);
    }

    /**
     * Test written when discovering that generate() uses '' as the default fragment,
     * which __invoke() considers invalid.
     */
    public function testCallingGenerateWithoutFragmentArgumentPassesNullValueForFragment()
    {
        $this->router->generateUri('foo', [], [])->willReturn('/foo');
        $helper = $this->createHelper();

        $this->assertEquals('/foo', $helper->generate('foo'));
    }

    /**
     * @group 42
     */
    public function testAppendsQueryStringAndFragmentWhenPresentAndRouteNameIsNotProvided()
    {
        $result = $this->prophesize(RouteResult::class);
        $result->isFailure()->willReturn(false);
        $result->getMatchedRouteName()->willReturn('matched-route');
        $result->getMatchedParams()->willReturn(['foo' => 'bar']);

        $this->router
            ->generateUri(
                'matched-route',
                ['foo' => 'baz'],
                []
            )
            ->willReturn('scheme://host/path');

        $helper = $this->createHelper();
        $helper->setRouteResult($result->reveal());

        $this->assertEquals(
            'scheme://host/path?query=params&are=present#fragment/exists',
            $helper(
                null,
                ['foo' => 'baz'],
                ['query' => 'params', 'are' => 'present'],
                'fragment/exists'
            )
        );
    }
}
