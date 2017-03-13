<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper\BodyParams;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BodyParamsMiddleware implements MiddlewareInterface
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
     * @return void
     */
    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * Clear all strategies from the middleware.
     *
     * @return void
     */
    public function clearStrategies()
    {
        $this->strategies = [];
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (in_array($request->getMethod(), $this->nonBodyRequests)) {
            return $delegate->process($request);
        }

        $header = $request->getHeaderLine('Content-Type');
        foreach ($this->strategies as $strategy) {
            if (! $strategy->match($header)) {
                continue;
            }

            // Matched! Parse and pass on to the next
            return $delegate->process($strategy->parse($request));
        }

        // No match; continue
        return $delegate->process($request);
    }
}
