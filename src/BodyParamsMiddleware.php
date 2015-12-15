<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BodyParamsMiddleware
{
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

        $header     = $request->getHeaderLine('Content-Type');
        $priorities = [
            'form'     => 'application/x-www-form-urlencoded',
            'json'     => '[/+]json',
        ];

        $matched = false;
        foreach ($priorities as $type => $pattern) {
            $pattern = sprintf('#%s#', $pattern);
            if (! preg_match($pattern, $header)) {
                continue;
            }
            $matched = $type;
            break;
        }

        switch ($matched) {
            case 'form':
                // $_POST is injected by default into the request body parameters.
                break;
            case 'json':
                $rawBody = $request->getBody()->getContents();
                return $next(
                    $request
                        ->withAttribute('rawBody', $rawBody)
                        ->withParsedBody(json_decode($rawBody, true)),
                    $response
                );
            default:
                break;
        }

        return $next($request, $response);
    }
}
