<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Helper\BodyParams;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
    private $body;

    /**
     * @var BodyParamsMiddleware
     */
    private $bodyParams;

    public function setUp()
    {
        $this->bodyParams = new BodyParamsMiddleware();

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, json_encode(['foo' => 'bar']));

        $this->body = new Stream($stream);
        $this->body->rewind();
    }

    private function mockHandler(callable $callback)
    {
        $handler = $this->prophesize(RequestHandlerInterface::class);

        $handler
            ->handle(Argument::type(ServerRequestInterface::class))
            ->will(function ($args) use ($callback) {
                $request = $args[0];
                return $callback($request);
            });

        return $handler;
    }

    private function mockHandlerToNeverTrigger()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class);

        $handler
            ->handle(Argument::type(ServerRequestInterface::class))
            ->shouldNotBeCalled();

        return $handler;
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
     *
     * @param string $contentType
     */
    public function testParsesRawBodyAndPreservesRawBodyInRequestAttribute($contentType)
    {
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => $contentType]);

        $this->bodyParams->process(
            $serverRequest,
            $this->mockHandler(function (ServerRequestInterface $request) use (&$serverRequest) {
                $serverRequest = $request;
                return new Response();
            })->reveal()
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
     *
     * @param string $method
     * @param string $contentType
     */
    public function testRequestIsUnchangedWhenBodyParamsMiddlewareIsNotApplicable($method, $contentType)
    {
        $originalRequest = new ServerRequest([], [], '', $method, $this->body, ['Content-type' => $contentType]);
        $finalRequest = null;

        $this->bodyParams->process(
            $originalRequest,
            $this->mockHandler(function (ServerRequestInterface $request) use (&$finalRequest) {
                $finalRequest = $request;
                return new Response();
            })->reveal()
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
        $expectedResponse = new Response();
        $strategy = $this->prophesize(StrategyInterface::class);
        $strategy->match('foo/bar')->willReturn(true);
        $strategy->parse($serverRequest)->willReturn($expectedReturn);
        $middleware->addStrategy($strategy->reveal());

        $response = $middleware->process(
            $serverRequest,
            $this->mockHandler(function (ServerRequestInterface $request) use ($expectedReturn, $expectedResponse) {
                $this->assertSame($expectedReturn, $request);
                return $expectedResponse;
            })->reveal()
        );

        $this->assertSame($expectedResponse, $response);
    }

    public function testCallsNextWithOriginalRequestWhenNoStrategiesMatch()
    {
        $middleware = $this->bodyParams;
        $middleware->clearStrategies();
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => 'foo/bar']);
        $expectedResponse = new Response();

        $response = $middleware->process(
            $serverRequest,
            $this->mockHandler(function (ServerRequestInterface $request) use ($serverRequest, $expectedResponse) {
                $this->assertSame($serverRequest, $request);
                return $expectedResponse;
            })->reveal()
        );

        $this->assertSame($expectedResponse, $response);
    }

    public function testThrowsMalformedRequestBodyExceptionWhenRequestBodyIsNotValidJson()
    {
        $expectedException = new MalformedRequestBodyException('malformed request body');

        $middleware = $this->bodyParams;
        $serverRequest = new ServerRequest([], [], '', 'PUT', $this->body, ['Content-type' => 'foo/bar']);
        $strategy = $this->prophesize(StrategyInterface::class);
        $strategy->match('foo/bar')->willReturn(true);
        $strategy->parse($serverRequest)->willThrow($expectedException);
        $middleware->addStrategy($strategy->reveal());

        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());
        $this->expectExceptionCode($expectedException->getCode());

        $middleware->process(
            $serverRequest,
            $this->mockHandlerToNeverTrigger()->reveal()
        );
    }

    public function jsonBodyRequests()
    {
        return [
            'POST'   => ['POST'],
            'PUT'    => ['PUT'],
            'PATCH'  => ['PATCH'],
            'DELETE' => ['DELETE'],
        ];
    }

    /**
     * @dataProvider jsonBodyRequests
     * @param string $method
     */
    public function testParsesJsonBodyWhenExpected($method)
    {
        $stream = fopen('php://memory', 'wb+');
        fwrite($stream, json_encode(['foo' => 'bar']));
        $body = new Stream($stream);

        $serverRequest = new ServerRequest(
            [],
            [],
            '',
            $method,
            $body,
            ['Content-type' => 'application/json;charset=utf-8']
        );

        $handlerTriggered = false;

        $result = $this->bodyParams->process(
            $serverRequest,
            $this->mockHandler(function (ServerRequestInterface $request) use ($serverRequest, &$handlerTriggered) {
                $handlerTriggered = true;

                $this->assertNotSame(
                    $request,
                    $serverRequest,
                    'Request passed to handler is the same as the one passed to BodyParamsMiddleware and should not be'
                );

                $this->assertSame(
                    json_encode(['foo' => 'bar']),
                    $request->getAttribute('rawBody'),
                    'Request passed to handler does not contain expected rawBody contents'
                );

                $this->assertSame(
                    ['foo' => 'bar'],
                    $request->getParsedBody(),
                    'Request passed to handler does not contain expected parsed body'
                );

                return new Response();
            })->reveal()
        );

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($handlerTriggered);
    }
}
