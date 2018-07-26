<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Helper\Exception\MalformedRequestBodyException;

use function array_shift;
use function explode;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function sprintf;
use function trim;

use const JSON_ERROR_NONE;

class JsonStrategy implements StrategyInterface
{
    public function match(string $contentType) : bool
    {
        return (bool) preg_match('#^application/(|[\S]+\+)json($|[ ;])#', $contentType);
    }

    /**
     * {@inheritDoc}
     *
     * @throws MalformedRequestBodyException
     */
    public function parse(ServerRequestInterface $request) : ServerRequestInterface
    {
        $rawBody = (string) $request->getBody();

        if (empty($rawBody)) {
            return $request
                ->withAttribute('rawBody', $rawBody)
                ->withParsedBody(null);
        }

        $parsedBody = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MalformedRequestBodyException(sprintf(
                'Error when parsing JSON request body: %s',
                json_last_error_msg()
            ));
        }

        return $request
            ->withAttribute('rawBody', $rawBody)
            ->withParsedBody($parsedBody);
    }
}
