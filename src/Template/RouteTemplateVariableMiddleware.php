<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper\Template;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

/**
 * Inject the currently matched route into the template variable container.
 *
 * This middleware relies on the TemplateVariableContainerMiddleware preceding
 * it in the middleware pipeline, or having the TemplateVariableContainer
 * request attribute present.
 *
 * If it finds a RouteResult request attribute, it will inject the return
 * value of `getMatchedRoute()` under the name `route` in the template variable
 * container.
 *
 * Templates rendered using the container can then access that value. It will
 * either be a Zend\Expressive\Router\Route instance, or empty.
 *
 * This middleware can replace the `UrlHelperMiddleware` in your pipeline.
 */
class RouteTemplateVariableMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $container = $request->getAttribute(TemplateVariableContainer::class);
        if (! $container) {
            return $handler->handle($request);
        }

        $routeResult = $request->getAttribute(RouteResult::class, null);
        $route = $routeResult
            ? $routeResult->getMatchedRoute()
            : null;

        return $handler->handle($request->withAttribute(
            TemplateVariableContainer::class,
            $container->with('route', $route)
        ));
    }
}
