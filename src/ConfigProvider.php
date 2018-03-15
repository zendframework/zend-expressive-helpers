<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'invokables' => [
                ServerUrlHelper::class => ServerUrlHelper::class,
            ],
            'factories'  => [
                ServerUrlMiddleware::class => ServerUrlMiddlewareFactory::class,
                UrlHelper::class           => UrlHelperFactory::class,
                UrlHelperMiddleware::class => UrlHelperMiddlewareFactory::class,
            ],
        ];
    }
}
