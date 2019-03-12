<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper;

use InvalidArgumentException;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

use function array_merge;
use function count;
use function http_build_query;
use function ltrim;
use function preg_match;
use function sprintf;

class UrlHelper
{
    /**
     * Regular expression used to validate fragment identifiers.
     *
     * @see RFC 3986: https://tools.ietf.org/html/rfc3986#section-3.5
     */
    const FRAGMENT_IDENTIFIER_REGEX = '/^([!$&\'()*+,;=._~:@\/?-]|%[0-9a-fA-F]{2}|[a-zA-Z0-9])+$/';

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
     * @param array $options Can have the following keys:
     *     - router (array): contains options to be passed to the router
     *     - reuse_result_params (bool): indicates if the current RouteResult
     *       parameters will be used, defaults to true
     * @throws Exception\RuntimeException for attempts to use the currently matched
     *     route but routing failed.
     * @throws Exception\RuntimeException for attempts to use a matched result
     *     when none has been previously injected in the instance.
     * @throws InvalidArgumentException for malformed fragment identifiers.
     */
    public function __invoke(
        string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        string $fragmentIdentifier = null,
        array $options = []
    ) : string {
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

        // Get the options to be passed to the router
        $routerOptions = $options['router'] ?? [];

        if ($routeName === null) {
            $path = $basePath . $this->generateUriFromResult($routeParams, $result, $routerOptions);
            $path = $this->appendQueryStringArguments($path, $queryParams);
            $path = $this->appendFragment($path, $fragmentIdentifier);
            return $path;
        }

        $reuseResultParams = ! isset($options['reuse_result_params']) || (bool) $options['reuse_result_params'];

        if ($result && $reuseResultParams) {
            // Merge RouteResult with the route parameters
            $routeParams = $this->mergeParams($routeName, $result, $routeParams);
        }

        // Generate the route
        $path = $basePath . $this->router->generateUri($routeName, $routeParams, $routerOptions);

        // Append query string arguments and fragment, if present
        $path = $this->appendQueryStringArguments($path, $queryParams);
        $path = $this->appendFragment($path, $fragmentIdentifier);

        return $path;
    }

    /**
     * Generate a URL based on a given route.
     *
     * Proxies to __invoke().
     *
     * @see UrlHelper::__invoke()
     */
    public function generate(
        string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        string $fragmentIdentifier = null,
        array $options = []
    ) : string {
        return $this($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options);
    }

    /**
     * Inject a route result.
     *
     * When the route result is injected, the helper will use it to seed default
     * parameters if the URL being generated is for the route that was matched.
     */
    public function setRouteResult(RouteResult $result) : void
    {
        $this->result = $result;
    }

    /**
     * Set the base path to prepend to a generated URI
     */
    public function setBasePath(string $path) : void
    {
        $this->basePath = '/' . ltrim($path, '/');
    }

    public function getRouteResult() : ?RouteResult
    {
        return $this->result;
    }

    /**
     * Internal accessor for retrieving the base path.
     */
    public function getBasePath() : string
    {
        return $this->basePath;
    }

    /**
     * @throws Exception\RuntimeException if current result is a routing failure.
     */
    private function generateUriFromResult(array $params, RouteResult $result, array $routerOptions) : string
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
     * @param array $params Parameters provided at invocation.
     */
    private function mergeParams(string $route, RouteResult $result, array $params) : array
    {
        if ($result->isFailure()) {
            return $params;
        }

        if ($result->getMatchedRouteName() !== $route) {
            return $params;
        }

        return array_merge($result->getMatchedParams(), $params);
    }

    /**
     * Append query string arguments to a URI string, if any are present.
     */
    private function appendQueryStringArguments(string $uriString, array $queryParams) : string
    {
        if (count($queryParams) > 0) {
            return sprintf('%s?%s', $uriString, http_build_query($queryParams));
        }
        return $uriString;
    }

    /**
     * Append a fragment to a URI string, if present.
     *
     * @throws InvalidArgumentException if the fragment identifier is malformed.
     */
    private function appendFragment(string $uriString, ?string $fragmentIdentifier) : string
    {
        if ($fragmentIdentifier !== null) {
            if (! preg_match(self::FRAGMENT_IDENTIFIER_REGEX, $fragmentIdentifier)) {
                throw new InvalidArgumentException('Fragment identifier must conform to RFC 3986', 400);
            }

            return sprintf('%s#%s', $uriString, $fragmentIdentifier);
        }
        return $uriString;
    }
}
