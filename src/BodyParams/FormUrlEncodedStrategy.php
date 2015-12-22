<?php
/**
 * @see       http://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ServerRequestInterface;

class FormUrlEncodedStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function match($contentType)
    {
        return (bool) preg_match('#^application/x-www-form-urlencoded($|[ ;])#', $contentType);
    }

    /**
     * {@inheritDoc}
     */
    public function parse(ServerRequestInterface $request)
    {
        return $request;
    }
}
