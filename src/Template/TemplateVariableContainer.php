<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper\Template;

use Countable;

/**
 * Stateful container for managing template variables within a middleware pipeline.
 *
 * `Zend\Expressive\Template\TemplateRendererInterface::addDefaultParam()` alters
 * the state of the renderer, which can be problematic in async environments.
 * This class can be composed in a request attribute (we recommend one named
 * after the class itself) in order to aggregate template variables prior to
 * rendering. The class that renders a template (generally a handler, but
 * potentially middleware) can then pull this container and use it to seed the
 * template variables.
 *
 * Middleware that needs to populate one or more template variables can do the
 * following:
 *
 * <code>
 * $container = $request->getAttribute(
 *     TemplateVariableContainer::class,
 *     new TemplateVariableContainer()
 * );
 *
 * // Populate a single variable:
 * $container->set('user', $user);
 *
 * // Populate several variables:
 * $container->merge([
 *     'user'  => $user,
 *     'roles' => $user->getRoles(),
 * ]);
 *
 * return $handler->handle($request->withAttribute(
 *     TemplateVariableContainer::class,
 *     $container
 * ));
 * </code>
 *
 * In a handler or middleware that renders a template, pull the container, and
 * use its mergeForTemplate() method when calling render():
 *
 * <code>
 * $container = $request->getAttribute(
 *     TemplateVariableContainer::class,
 *     new TemplateVariableContainer()
 * );
 *
 * $content = $this->renderer->render(
 *     'some::template',
 *     $container->mergeForTemplate([
 *         'local' => 'value',
 *     ])
 * );
 * </code>
 */
class TemplateVariableContainer implements Countable
{
    /**
     * @var array<string, mixed>
     */
    private $variables = [];

    public function count() : int
    {
        return count($this->variables);
    }

    /**
     * @return null|mixed Returns null if $key does not exist in container;
     *     otherwise, returns value associated with $key
     */
    public function get(string $key)
    {
        return $this->variables[$key] ?? null;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->variables);
    }

    public function set(string $key, $value) : void
    {
        $this->variables[$key] = $value;
    }

    public function unset(string $key) : void
    {
        unset($this->variables[$key]);
    }

    /**
     * Merge an array of values into the container.
     *
     * Use this method to populate many values in the container at once.
     *
     * This method will overwrite any existing value with the same key with the
     * new value if it occurs in $values.
     */
    public function merge(array $values) : void
    {
        $this->variables = array_merge($this->variables, $values);
    }

    /**
     * Merge a set of values with those in the container, and return the result.
     *
     * Use this method to merge handler-specific template values with those in
     * the container in order to pass the result to the renderer's `render()`
     * method.
     */
    public function mergeForTemplate(array $values) : array
    {
        return array_merge($this->variables, $values);
    }
}
