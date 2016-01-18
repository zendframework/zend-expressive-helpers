<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Interop\Container\ContainerInterface;

class UrlHelperMiddlewareFactory
{
    /**
     * Create and return a UrlHelperMiddleware instance.
     *
     * @param ContainerInterface $container
     * @return UrlHelperMiddleware
     * @throws Exception\MissingHelperException if the UrlHelper service is
     *     missing
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(UrlHelper::class)) {
            throw new Exception\MissingHelperException(sprintf(
                '%s requires a %s service at instantiation; none found',
                UrlHelperMiddleware::class,
                UrlHelper::class
            ));
        }

        return new UrlHelperMiddleware($container->get(UrlHelper::class));
    }
}
