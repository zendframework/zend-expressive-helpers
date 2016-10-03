<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Helper\BodyParams;

use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Helper\BodyParams\StrategyInterface;
use Zend\Expressive\Helper\Exception\MalformedRequestBodyException;

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

    public function jsonProvider()
    {
        return [
            ['application/json'],
            ['application/hal+json'],
            ['application/vnd.resource.v2+json'],
        ];
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testParsesRawBodyAndPreservesRawBodyInRequestAttribute($contentType)
    {
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => $contentType]);

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
            ['GET', 'application/x-www-form-urlencoded'],
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

    public function testCanClearStrategies()
    {
        $this->bodyParams->clearStrategies();
        $this->assertAttributeSame([], 'strategies', $this->bodyParams);
    }

    public function testCanAttachCustomStrategies()
    {
        $strategy = $this->prophesize(StrategyInterface::class)->reveal();
        $this->bodyParams->addStrategy($strategy);
        $this->assertAttributeContains($strategy, 'strategies', $this->bodyParams);
    }

    public function testCustomStrategiesCanMatchRequests()
    {
        $middleware = $this->bodyParams;
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => 'foo/bar']);
        $expectedReturn = $this->prophesize(ServerRequestInterface::class)->reveal();
        $strategy = $this->prophesize(StrategyInterface::class);
        $strategy->match('foo/bar')->willReturn(true);
        $strategy->parse($serverRequest)->willReturn($expectedReturn);
        $middleware->addStrategy($strategy->reveal());

        $triggered = false;
        $middleware(
            $serverRequest,
            new Response(),
            function ($request, $response) use (&$triggered, $expectedReturn) {
                $this->assertSame($expectedReturn, $request);
                $triggered = true;
                return $response;
            }
        );

        $this->assertTrue($triggered, 'Next was not triggered');
    }

    public function testCallsNextWithOriginalRequestWhenNoStrategiesMatch()
    {
        $middleware = $this->bodyParams;
        $middleware->clearStrategies();
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => 'foo/bar']);

        $triggered = false;
        $middleware(
            $serverRequest,
            new Response(),
            function ($request, $response) use (&$triggered, $serverRequest) {
                $this->assertSame($serverRequest, $request);
                $triggered = true;
                return $response;
            }
        );

        $this->assertTrue($triggered, 'Next was not triggered');
    }

    public function testThrowsMalformedRequestBodyExceptionWhenRequestBodyIsNotValidJson()
    {
        $expectedException = new MalformedRequestBodyException('malformed request body');

        $this->setExpectedException(
            get_class($expectedException),
            $expectedException->getMessage(),
            $expectedException->getCode()
        );

        $middleware = $this->bodyParams;
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => 'foo/bar']);
        $strategy = $this->prophesize(StrategyInterface::class);
        $strategy->match('foo/bar')->willReturn(true);
        $strategy->parse($serverRequest)->willThrow($expectedException);
        $middleware->addStrategy($strategy->reveal());

        $middleware($serverRequest, new Response(),
            function ($request, $response) use (&$triggered) {
                $triggered = true;
                return $response;
            }
        );

        $this->assertFalse($triggered, 'Next should not have been triggered!');
    }
}
