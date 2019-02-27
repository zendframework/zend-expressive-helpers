<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Helper\Template;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Helper\Template\TemplateVariableContainer;

class TemplateVariableContainerTest extends TestCase
{
    public function setUp()
    {
        $this->container = new TemplateVariableContainer();
    }

    public function testContainerIsEmptyByDefault()
    {
        $this->assertCount(0, $this->container);
    }

    public function testCanSetVariables() : TemplateVariableContainer
    {
        $this->container->set('key', 'value');
        $this->assertCount(1, $this->container);
        $this->assertTrue($this->container->has('key'));
        $this->assertSame('value', $this->container->get('key'));
        return $this->container;
    }

    public function testHasReturnsFalseForUnsetVariables()
    {
        $this->assertFalse($this->container->has('key'));
    }

    public function testGetReturnsNullForUnsetVariables()
    {
        $this->assertNull($this->container->get('key'));
    }

    /**
     * @depends testCanSetVariables
     */
    public function testCanUnsetVariableFromContainer(TemplateVariableContainer $container)
    {
        $container->unset('key');
        $this->assertFalse($container->has('key'));
    }

    public function testCanMergeArrayIntoContainer()
    {
        $values = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat',
        ];

        $this->container->merge($values);

        foreach ($values as $key => $value) {
            $this->assertTrue($this->container->has($key));
            $this->assertEquals($value, $this->container->get($key));
        }
    }

    public function testWillReturnArrayWhenRequestedToMergeForTemplate()
    {
        $containerValues = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat',
        ];

        $localValues = [
            'foo'  => 'FOO',
            'else' => 'something',
        ];

        $expected = array_merge($containerValues, $localValues);

        $this->container->merge($containerValues);

        $merged = $this->container->mergeForTemplate($localValues);

        $this->assertSame($expected, $merged);
    }
}
