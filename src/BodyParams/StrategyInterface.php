<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

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
     * @return bool Whether or not the strategy matches.
     */
    public function match(string $contentType) : bool;

    /**
     * Parse the body content and return a new request.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request) : ServerRequestInterface;
}
