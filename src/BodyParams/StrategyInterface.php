<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface defining a body parameter strategy.
 */
interface StrategyInterface
{
    /**
     * Match the content type to the strategy criteria.
     *
     * @param string $contentType
     * @return bool Whether or not the strategy matches.
     */
    public function match($contentType);

    /**
     * Parse the body content and return a new request.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request);
}
