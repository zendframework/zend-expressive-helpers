<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerUrlMiddleware implements MiddlewareInterface
{
    /**
     * @var ServerUrlHelper
     */
    private $helper;

    /**
     * @param ServerUrlHelper $helper
     */
    public function __construct(ServerUrlHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Inject the ServerUrlHelper instance with the request URI.
     *
     * Injects the ServerUrlHelper with the incoming request URI, and then invoke
     * the next middleware.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->helper->setUri($request->getUri());

        return $delegate->process($request);
    }
}
