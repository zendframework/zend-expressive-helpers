<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Expressive\Helper\BodyParamsMiddleware;

class BodyParamsMiddlewareTest extends TestCase
{
    /**
     * @var Stream
     */
    protected $body;

     /**
     * @var BodyParamsMiddleware
     */
    protected $bodyParams;

    public function setUp()
    {
        $this->bodyParams = new BodyParamsMiddleware();

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, json_encode(['foo' => 'bar']));

        $this->body = new Stream($stream);
        $this->body->rewind();
    }

    public function testParsesRawBodyAndPreservesRawBodyInRequestAttribute()
    {
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => 'application/json']);

        $this->bodyParams->__invoke(
            $serverRequest,
            new Response(),
            function ($request, $response) use (&$serverRequest) {
                $serverRequest = $request;

                return $response;
            }
        );

        $this->assertSame(
            json_encode(['foo' => 'bar']),
            $serverRequest->getAttribute('rawBody')
        );
        $this->assertSame(['foo' => 'bar'], $serverRequest->getParsedBody());
    }

    public function notApplicableProvider()
    {
        return [
            ['GET', 'application/json'],
            ['HEAD', 'application/json'],
            ['OPTIONS', 'application/json'],
            ['POST', 'application/x-www-form-urlencoded'],
            ['DELETE', 'this-isnt-a-real-content-type'],
        ];
    }

    /**
     * @dataProvider notApplicableProvider
     */
    public function testRequestIsUnchangedWhenBodyParamsMiddlewareIsNotApplicable($method, $contentType)
    {
        $originalRequest = new ServerRequest([], [], '', $method, $this->body, ['Content-type' => $contentType]);
        $finalRequest = null;

        $this->bodyParams->__invoke(
            $originalRequest,
            new Response(),
            function ($request, $response) use (&$finalRequest) {
                $finalRequest = $request;

                return $response;
            }
        );

        $this->assertSame($originalRequest, $finalRequest);
    }
}
