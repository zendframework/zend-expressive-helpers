<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ServerRequestInterface;

use function parse_str;
use function preg_match;

class FormUrlEncodedStrategy implements StrategyInterface
{
    public function match(string $contentType) : bool
    {
        return 1 === preg_match('#^application/x-www-form-urlencoded($|[ ;])#', $contentType);
    }

    public function parse(ServerRequestInterface $request) : ServerRequestInterface
    {
        $parsedBody = $request->getParsedBody();

        if (! empty($parsedBody)) {
            return $request;
        }

        $rawBody = (string) $request->getBody();

        if (empty($rawBody)) {
            return $request;
        }

        parse_str($rawBody, $parsedBody);

        return $request->withParsedBody($parsedBody);
    }
}
