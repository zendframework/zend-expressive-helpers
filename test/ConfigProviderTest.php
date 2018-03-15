<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Helper;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Helper\ConfigProvider;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperMiddleware;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    private $provider;

    protected function setUp() : void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray() : array
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);

        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config) : void
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);

        $this->assertArrayHasKey('invokables', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['invokables']);
        $this->assertArrayHasKey(ServerUrlHelper::class, $config['dependencies']['invokables']);

        $this->assertArrayHasKey('factories', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['factories']);
        $this->assertArrayHasKey(ServerUrlMiddleware::class, $config['dependencies']['factories']);
        $this->assertArrayHasKey(UrlHelper::class, $config['dependencies']['factories']);
        $this->assertArrayHasKey(UrlHelperMiddleware::class, $config['dependencies']['factories']);
    }
}
