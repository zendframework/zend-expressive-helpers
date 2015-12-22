<?php
/**
 * @see       http://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ServerRequestInterface;

class JsonStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function match($contentType)
    {
        $parts = explode(';', $contentType);
        $mime = array_shift($parts);
        return (bool) preg_match('#[/+]json$#', trim($mime));
    }

    /**
     * {@inheritDoc}
     */
    public function parse(ServerRequestInterface $request)
    {
        $rawBody = $request->getBody()->getContents();
        return $request
            ->withAttribute('rawBody', $rawBody)
            ->withParsedBody(json_decode($rawBody, true));
    }
}
