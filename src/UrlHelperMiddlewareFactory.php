<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Router\RouteResultSubjectInterface;

class UrlHelperMiddlewareFactory
{
    /**
     * Create and return a UrlHelperMiddleware instance.
     *
     * @param ContainerInterface $container
     * @return UrlHelperMiddleware
     * @throws Exception\MissingHelperException if the UrlHelper service is
     *     missing
     * @throws Exception\MissingSubjectException if the
     *     RouteResultSubjectInterface service is missing
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

        $subjectService = $this->getSubjectService($container);

        return new UrlHelperMiddleware(
            $container->get(UrlHelper::class),
            $container->get($subjectService)
        );
    }

    /**
     * Determine the name of the service returning the RouteResultSubjectInterface instance.
     *
     * Checks against:
     *
     * - RouteResultSubjectInterface
     * - Application
     *
     * returning the first that is found in the container.
     *
     * If neither is found, raises an exception.
     *
     * @param ContainerInterface $container
     * @return string
     * @throws Exception\MissingSubjectException
     */
    private function getSubjectService(ContainerInterface $container)
    {
        if ($container->has(RouteResultSubjectInterface::class)) {
            return RouteResultSubjectInterface::class;
        }

        if ($container->has(Application::class)) {
            return Application::class;
        }

        throw new Exception\MissingSubjectException(sprintf(
            '%s requires a %s service at instantiation; none found',
            UrlHelperMiddleware::class,
            UrlHelper::class
        ));
    }
}
