<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResultSubjectInterface;

/**
 * Pipeline middleware for attaching a UrlHelper to a
 * RouteResultSubjectInterface instance.
 */
class UrlHelperMiddleware
{
    /**
     * @var UrlHelper
     */
    private $helper;

    /**
     * @var RouteResultSubjectInterface
     */
    private $subject;

    /**
     * @param UrlHelper $helper
     * @param RouteResultSubjectInterface $subject
     */
    public function __construct(UrlHelper $helper, RouteResultSubjectInterface $subject)
    {
        $this->helper = $helper;
        $this->subject = $subject;
    }

    /**
     * Attach the UrlHelper instance as an observer to the RouteResultSubjectInterface
     *
     * Attaches the helper, and then dispatches the next middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->subject->attachRouteResultObserver($this->helper);
        return $next($request, $response);
    }
}
