<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BodyParamsMiddleware
{
    /**
     * @var StrategyInterface[]
     */
    private $strategies = [];

    /**
     * List of request methods that do not have any defined body semantics, and thus
     * will not have the body parsed.
     *
     * @see https://tools.ietf.org/html/rfc7231
     *
     * @var array
     */
    private $nonBodyRequests = [
        'GET',
        'HEAD',
        'OPTIONS',
    ];

    /**
     * Constructor
     *
     * Registers the form and json strategies.
     */
    public function __construct()
    {
        $this->addStrategy(new FormUrlEncodedStrategy());
        $this->addStrategy(new JsonStrategy());
    }

    /**
     * Add a body parsing strategy to the middleware.
     *
     * @param StrategyInterface $strategy
     */
    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * Clear all strategies from the middleware.
     */
    public function clearStrategies()
    {
        $this->strategies = [];
    }

    /**
     * Adds JSON decoded request body to the request, where appropriate.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (in_array($request->getMethod(), $this->nonBodyRequests)) {
            return $next($request, $response);
        }

        $header = $request->getHeaderLine('Content-Type');
        foreach ($this->strategies as $strategy) {
            if (! $strategy->match($header)) {
                continue;
            }

            // Matched! Parse and pass on to the next
            return $next(
                $strategy->parse($request),
                $response
            );
        }

        // No match; continue
        return $next($request, $response);
    }
}
