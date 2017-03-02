<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Psr\Http\Message\UriInterface;

/**
 * Helper class for generating a fully-qualified URI when provided a path.
 */
class ServerUrlHelper
{
    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * Return a path relative to the current request URI.
     *
     * If no request URI has been injected, it returns an absolute path
     * only; relative paths are made absolute, and absolute paths are returned
     * verbatim (null paths are returned as root paths).
     *
     * Otherwise, returns a fully-qualified URI based on the injected request
     * URI; absolute paths replace the request URI path, while relative paths
     * are appended to it (and null paths are considered the current path).
     *
     * The $path may optionally contain the query string and/or fragment to
     * use.
     *
     * @param null|string $path
     * @return string
     */
    public function __invoke($path = null)
    {
        if ($this->uri instanceof UriInterface) {
            return $this->createUrlFromUri($path);
        }

        if (empty($path)) {
            return '/';
        }

        if ('/' === $path[0]) {
            return $path;
        }

        return '/' . $path;
    }

    /**
     * Generate a path relative to the current request URI.
     *
     * Proxies to __invoke().
     *
     * @param null|string $path
     * @return string
     */
    public function generate($path = null)
    {
        return $this($path);
    }

    /**
     * @param UriInterface $uri
     * @return void
     */
    public function setUri(UriInterface $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param string $specification
     * @return string
     */
    private function createUrlFromUri($specification)
    {
        preg_match(
            '%^(?P<path>[^?#]*)(?:(?:\?(?P<query>[^#]*))?(?:\#(?P<fragment>.*))?)$%',
            (string) $specification,
            $matches
        );
        $path     = $matches['path'];
        $query    = isset($matches['query']) ? $matches['query'] : '';
        $fragment = isset($matches['fragment']) ? $matches['fragment'] : '';

        $uri = $this->uri
            ->withQuery('')
            ->withFragment('');

        // Relative path
        if (! empty($path) && '/' !== $path[0]) {
            $path = rtrim($this->uri->getPath(), '/') . '/' . $path;
        }

        // Path present; set on URI
        if (! empty($path)) {
            $uri = $uri->withPath($path);
        }

        // Query present; set on URI
        if (! empty($query)) {
            $uri = $uri->withQuery($query);
        }

        // Fragment present; set on URI
        if (! empty($fragment)) {
            $uri = $uri->withFragment($fragment);
        }

        return (string) $uri;
    }
}
