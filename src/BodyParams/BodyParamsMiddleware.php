<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;

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
     */
    public function addStrategy(StrategyInterface $strategy) : void
    {
        $this->strategies[] = $strategy;
    }

    /**
     * Clear all strategies from the middleware.
     */
    public function clearStrategies() : void
    {
        $this->strategies = [];
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if (in_array($request->getMethod(), $this->nonBodyRequests)) {
            return $handler->handle($request);
        }

        $header = $request->getHeaderLine('Content-Type');
        foreach ($this->strategies as $strategy) {
            if (! $strategy->match($header)) {
                continue;
            }

            // Matched! Parse and pass on to the next
            return $handler->handle($strategy->parse($request));
        }

        // No match; continue
        return $handler->handle($request);
    }
}
