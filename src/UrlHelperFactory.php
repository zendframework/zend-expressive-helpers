<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

use function sprintf;

class UrlHelperFactory
{
    /** @var string Base path for the URL helper */
    private $basePath;

    /** @var string $routerServiceName */
    private $routerServiceName;

    /**
     * Allow serialization
     */
    public static function __set_state(array $data) : self
    {
        return new self(
            $data['basePath'] ?? '/',
            $data['routerServiceName'] ?? RouterInterface::class
        );
    }

    /**
     * Allows varying behavior per-instance.
     *
     * Defaults to '/' for the base path, and the FQCN of the RouterInterface.
     */
    public function __construct(string $basePath = '/', string $routerServiceName = RouterInterface::class)
    {
        $this->basePath = $basePath;
        $this->routerServiceName = $routerServiceName;
    }

    /**
     * Create a UrlHelper instance.
     *
     * @throws Exception\MissingRouterException
     */
    public function __invoke(ContainerInterface $container) : UrlHelper
    {
        if (! $container->has($this->routerServiceName)) {
            throw new Exception\MissingRouterException(sprintf(
                '%s requires a %s implementation; none found in container',
                UrlHelper::class,
                $this->routerServiceName
            ));
        }

        $helper = new UrlHelper($container->get($this->routerServiceName));
        $helper->setBasePath($this->basePath);
        return $helper;
    }
}
