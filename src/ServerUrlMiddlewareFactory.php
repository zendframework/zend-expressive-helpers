<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Interop\Container\ContainerInterface;

class ServerUrlMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ServerUrlHelper::class)) {
            throw new Exception\MissingHelperException(sprintf(
                '%s requires a %s service at instantiation; none found',
                ServerUrlMiddleware::class,
                ServerUrlHelper::class
            ));
        }
        return new ServerUrlMiddleware($container->get(ServerUrlHelper::class));
    }
}
