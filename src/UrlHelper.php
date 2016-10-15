<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use InvalidArgumentException;
use Zend\Expressive\Router\Exception\RuntimeException as RouterException;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

class UrlHelper
{
    /**
     * @var string
     */
    private $basePath = '/';

    /**
     * @var RouteResult
     */
    private $result;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Generate a URL based on a given route.
     *
     * @param string $routeName
     * @param array $params     The parameters to build the url
     *                          can have the following keys: route, query, fragment
     * @param array $options    Can have the following keys: router, reuse_result_params
     *                          - router must be an array containing the router options
     *                          - reuse_result_params is a boolean to indicate if the
     *                            current RouteResult parameters will be used, defaults to true
     *
     * @return string
     * @throws Exception\RuntimeException if no route provided, and no result match
     *     present.
     * @throws Exception\RuntimeException if no route provided, and result match is a
     *     routing failure.
     * @throws RouterException if router cannot generate URI for given route.
     */
    public function __invoke($routeName = null, $params = [], $options = [])
    {
        $result = $this->getRouteResult();
        if ($routeName === null && $result === null) {
            throw new Exception\RuntimeException(
                'Attempting to use matched result when none was injected; aborting'
            );
        }

        $basePath = $this->getBasePath();
        if ($basePath === '/') {
            $basePath = '';
        }

        $query = null;
        $fragment = null;

        // Check if the route key exists, otherwise treat $params as v2 behavior
        if (array_key_exists('route', $params) && is_array($params['route'])) {
            if (array_key_exists('query', $params) && is_array($params['query'])) {
                $query = $params['query'];
            }

            if (array_key_exists('fragment', $params)) {
                $fragment = $params['fragment'];
            }

            $params = $params['route'];
        }

        $routerOptions = array_key_exists('router', $options) ? $options['router'] : [];

        if ($routeName === null) {
            return $basePath . $this->generateUriFromResult($params, $result, $routerOptions);
        }

        if ($result
            && (! array_key_exists('reuse_result_params', $options) || $options['reuse_result_params'] !== false)
        ) {
            $params = $this->mergeParams($routeName, $result, $params);
        }

        $path = $basePath . $this->router->generateUri($routeName, $params, $routerOptions);

        if ($query !== null) {
            $path .= '?' . http_build_query($query);
        }

        if ($fragment !== null) {
            $path .= '#' . $fragment;
        }

        return $path;
    }

    /**
     * Generate a URL based on a given route.
     *
     * Proxies to __invoke().
     *
     * @param string $route
     * @param array $params
     * @param array $routerOptions
     * @return string
     * @throws Exception\RuntimeException if no route provided, and no result match
     *     present.
     * @throws Exception\RuntimeException if no route provided, and result match is a
     *     routing failure.
     * @throws RouterException if router cannot generate URI for given route.
     */
    public function generate($route = null, array $params = [], array $routerOptions = [])
    {
        return $this($route, $params, $routerOptions);
    }

    /**
     * Inject a route result.
     *
     * When the route result is injected, the helper will use it to seed default
     * parameters if the URL being generated is for the route that was matched.
     *
     * @param RouteResult $result
     */
    public function setRouteResult(RouteResult $result)
    {
        $this->result = $result;
    }

    /**
     * Set the base path to prepend to a generated URI
     */
    public function setBasePath($path)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException(sprintf(
                'Base path must be a string; received %s',
                (is_object($path) ? get_class($path) : gettype($path))
            ));
        }

        $this->basePath = '/' . ltrim($path, '/');
    }

    /**
     * Internal accessor for retrieving the route result.
     *
     * @return null|RouteResult
     */
    protected function getRouteResult()
    {
        return $this->result;
    }

    /**
     * Internal accessor for retrieving the base path.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param array $params
     * @param RouteResult $result
     * @param array $routerOptions
     * @return string
     * @throws RenderingException if current result is a routing failure.
     */
    private function generateUriFromResult(array $params, RouteResult $result, array $routerOptions)
    {
        if ($result->isFailure()) {
            throw new Exception\RuntimeException(
                'Attempting to use matched result when routing failed; aborting'
            );
        }

        $name   = $result->getMatchedRouteName();
        $params = array_merge($result->getMatchedParams(), $params);
        return $this->router->generateUri($name, $params, $routerOptions);
    }

    /**
     * Merge route result params and provided parameters.
     *
     * If the route result represents a routing failure, returns the params
     * verbatim.
     *
     * If the route result does not represent the same route name requested,
     * returns the params verbatim.
     *
     * Otherwise, merges the route result params with those provided at
     * invocation, with the latter having precedence.
     *
     * @param string $route Route name.
     * @param RouteResult $result
     * @param array $params Parameters provided at invocation.
     * @return array
     */
    private function mergeParams($route, RouteResult $result, array $params)
    {
        if ($result->isFailure()) {
            return $params;
        }

        if ($result->getMatchedRouteName() !== $route) {
            return $params;
        }

        return array_merge($result->getMatchedParams(), $params);
    }
}
