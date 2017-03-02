<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;
use Zend\Expressive\Helper\ServerUrlHelper;

class ServerUrlHelperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function plainPaths()
    {
        return [
            'null'          => [null,       '/'],
            'empty'         => ['',         '/'],
            'root'          => ['/',        '/'],
            'relative-path' => ['foo/bar',  '/foo/bar'],
            'abs-path'      => ['/foo/bar', '/foo/bar'],
        ];
    }

    /**
     * @dataProvider plainPaths
     *
     * @param null|string $path
     * @param string $expected
     */
    public function testInvocationReturnsPathOnlyIfNoUriInjected($path, $expected)
    {
        $helper = new ServerUrlHelper();
        $this->assertEquals($expected, $helper($path));
    }

    public function plainPathsForUseWithUri()
    {
        $uri = new Uri('https://example.com/resource');
        return [
            'null'          => [$uri, null,       'https://example.com/resource'],
            'empty'         => [$uri, '',         'https://example.com/resource'],
            'root'          => [$uri, '/',        'https://example.com/'],
            'relative-path' => [$uri, 'foo/bar',  'https://example.com/resource/foo/bar'],
            'abs-path'      => [$uri, '/foo/bar', 'https://example.com/foo/bar'],
        ];
    }

    /**
     * @dataProvider plainPathsForUseWithUri
     *
     * @param UriInterface $uri
     * @param null|string $path
     * @param string $expected
     */
    public function testInvocationReturnsUriComposingPathWhenUriInjected(UriInterface $uri, $path, $expected)
    {
        $helper = new ServerUrlHelper();
        $helper->setUri($uri);
        $this->assertEquals((string) $expected, $helper($path));
    }

    public function uriWithQueryString()
    {
        $uri = new Uri('https://example.com/resource?bar=baz');
        return [
            'null'          => [$uri, null,       'https://example.com/resource'],
            'empty'         => [$uri, '',         'https://example.com/resource'],
            'root'          => [$uri, '/',        'https://example.com/'],
            'relative-path' => [$uri, 'foo/bar',  'https://example.com/resource/foo/bar'],
            'abs-path'      => [$uri, '/foo/bar', 'https://example.com/foo/bar'],
        ];
    }

    /**
     * @dataProvider uriWithQueryString
     *
     * @param UriInterface $uri
     * @param null|string $path
     * @param string $expected
     */
    public function testStripsQueryStringFromInjectedUri(UriInterface $uri, $path, $expected)
    {
        $helper = new ServerUrlHelper();
        $helper->setUri($uri);
        $this->assertEquals($expected, $helper($path));
    }

    public function uriWithFragment()
    {
        $uri = new Uri('https://example.com/resource#bar');
        return [
            'null'          => [$uri, null,       'https://example.com/resource'],
            'empty'         => [$uri, '',         'https://example.com/resource'],
            'root'          => [$uri, '/',        'https://example.com/'],
            'relative-path' => [$uri, 'foo/bar',  'https://example.com/resource/foo/bar'],
            'abs-path'      => [$uri, '/foo/bar', 'https://example.com/foo/bar'],
        ];
    }

    /**
     * @dataProvider uriWithFragment
     *
     * @param UriInterface $uri
     * @param null|string $path
     * @param string $expected
     */
    public function testStripsFragmentFromInjectedUri(UriInterface $uri, $path, $expected)
    {
        $helper = new ServerUrlHelper();
        $helper->setUri($uri);
        $this->assertEquals($expected, $helper($path));
    }

    public function pathsWithQueryString()
    {
        $uri = new Uri('https://example.com/resource');
        return [
            'empty-path'    => [$uri, '?foo=bar',         'https://example.com/resource?foo=bar'],
            'root-path'     => [$uri, '/?foo=bar',        'https://example.com/?foo=bar'],
            'relative-path' => [$uri, 'foo/bar?foo=bar',  'https://example.com/resource/foo/bar?foo=bar'],
            'abs-path'      => [$uri, '/foo/bar?foo=bar', 'https://example.com/foo/bar?foo=bar'],
        ];
    }

    /**
     * @dataProvider pathsWithQueryString
     *
     * @param UriInterface $uri
     * @param string $path
     * @param string $expected
     */
    public function testUsesQueryStringFromProvidedPath(UriInterface $uri, $path, $expected)
    {
        $helper = new ServerUrlHelper();
        $helper->setUri($uri);
        $this->assertEquals($expected, $helper($path));
    }

    public function pathsWithFragment()
    {
        $uri = new Uri('https://example.com/resource');
        return [
            'empty-path'    => [$uri, '#bar',         'https://example.com/resource#bar'],
            'root-path'     => [$uri, '/#bar',        'https://example.com/#bar'],
            'relative-path' => [$uri, 'foo/bar#bar',  'https://example.com/resource/foo/bar#bar'],
            'abs-path'      => [$uri, '/foo/bar#bar', 'https://example.com/foo/bar#bar'],
        ];
    }

    /**
     * @dataProvider pathsWithFragment
     *
     * @param UriInterface $uri
     * @param string $path
     * @param string $expected
     */
    public function testUsesFragmentFromProvidedPath(UriInterface $uri, $path, $expected)
    {
        $helper = new ServerUrlHelper();
        $helper->setUri($uri);
        $this->assertEquals($expected, $helper($path));
    }

    public function testGenerateProxiesToInvokeMethod()
    {
        $path = '/foo';

        $helper = Mockery::mock(ServerUrlHelper::class)->shouldDeferMissing();
        $helper->shouldReceive('__invoke')
            ->once()
            ->with($path)
            ->andReturn('it worked');

        $this->assertSame('it worked', $helper->generate($path));
    }
}
