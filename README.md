# Helper classes for Expressive

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-helpers.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-helpers)

Helper classes for [Expressive](https://github.com/zendframework/zend-expressive).

## Installation

Install this library using composer:

```bash
$ composer require zendframework/zend-expressive-helpers
```

We recommend using a dependency injection container, and typehint against
[container-interop](https://github.com/container-interop/container-interop). We
can recommend the following implementations:

- [zend-servicemanager](https://github.com/zendframework/zend-servicemanager):
  `composer require zendframework/zend-servicemanager`
- [pimple-interop](https://github.com/moufmouf/pimple-interop):
  `composer require mouf/pimple-interop`
- [Aura.Di](https://github.com/auraphp/Aura.Di)

## Helpers Provided

### UrlHelper

`Zend\Expressive\Helper\UrlHelper` provides the ability to generate a URI path
based on a given route defined in the `Zend\Expressive\Router\RouterInterface`.
The provided `Zend\Expressive\Helper\UrlHelperMiddleware` can look for a
`Zend\Expressive\Router\RouteResult` request attribute, and, if present, inject
the `UrlHelper` with it; when this occurs, if the route being used to generate
a URI was also the one matched during routing, you can provide a subset of
routing parameters, and any not provided will be pulled from those matched.

In order to use the helper, you will need to instantiate it with the current
`RouterInterface`. The factory `Zend\Expressive\Helper\UrlHelperFactory` has
been provided for this purpose, and can be used trivially with most
dependency injection containers implementing container-interop:

```php
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperFactory;

// zend-servicemanager:
$services->setFactory(UrlHelper::class, UrlHelperFactory::class);

// Pimple:
$pimple[UrlHelper::class] = $pimple->share(function ($container) {
    $factory = new UrlHelperFactory();
    return $factory($container);
});

// Aura.Di:
$container->set(UrlHelperFactory::class, $container->lazyNew(UrlHelperFactory::class));
$container->set(
    UrlHelper::class,
    $container->lazyGetCall(UrlHelperFactory::class, '__invoke', $container)
);
```

The following dependency configuration will work for all three when using the
Expressive skeleton:

```php
return ['dependencies' => [
    'factories' => [
        UrlHelper::class => UrlHelperFactory::class,
    ],
]]
```

> #### Factory requires RouterInterface
>
> The factory requires that a service named `Zend\Expressive\Router\RouterInterface` is present,
> and will raise an exception if the service is not found.

For the helper to be useful, it must be injected with a
`Zend\Expressive\Router\RouteResult`. To automate this, we provide a middleware
class, `UrlHelperMiddleware`, which accepts the `UrlHelper` instance.
When invoked, it looks for a `RouteResult` request attribute, and, if found,
injects it into the `UrlHelper`. To register this middleware, you will need to:

- Register the `UrlHelperMiddleware` as a service in your container.
- Register the `UrlHelperMiddleware` as middleware between the Expressive
  routing and dispatch middleware.

The following examples demonstrate registering the services.

```php
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddlewareFactory;

// zend-servicemanager:
$services->setFactory(UrlHelperMiddleware::class, UrlHelperMiddlewareFactory::class);

// Pimple:
$pimple[UrlHelperMiddleware::class] = $pimple->share(function ($container) {
    $factory = new UrlHelperMiddlewareFactory();
    return $factory($container);
});

// Aura.Di:
$container->set(UrlHelperMiddlewareFactory::class, $container->lazyNew(UrlHelperMiddlewareFactory::class));
$container->set(
    UrlHelperMiddleware::class,
    $container->lazyGetCall(UrlHelperMiddlewareFactory::class, '__invoke', $container)
);
```

To register the `UrlHelperMiddleware`:

```php
use Zend\Expressive\Helper\UrlHelperMiddleware;

$app->pipeRoutingMiddleware();
$app->pipe(UrlHelperMiddleware::class);
$app->pipeDispatchMiddleware();

// Or use configuration:
// [
//     'middleware_pipeline' => [
//         'routing' => [
//             'middleware' => [
//                 Zend\Expressive\Container\ApplicationFactory::ROUTING_MIDDLEWARE,
//                 UrlHelperMiddleware::class,
//                 Zend\Expressive\Container\ApplicationFactory::DISPATCH_MIDDLEWARE,
//             ],
//             'priority' => 1,
//         ],
//     ],
// ]
```

The following dependency configuration will work for all three when using the
Expressive skeleton:

```php
return [
    'dependencies' => [
        'invokables' => [
        ],
        'factories' => [
            UrlHelper::class => UrlHelperFactory::class,
            UrlHelperMiddleware::class => UrlHelperMiddlewareFactory::class,
        ],
    ],
    'middleware_pipeline' => [
        'routing' => [
            'middleware' => [
                Zend\Expressive\Container\ApplicationFactory::ROUTING_MIDDLEWARE,
                UrlHelperMiddleware::class,
                Zend\Expressive\Container\ApplicationFactory::DISPATCH_MIDDLEWARE,
            ],
            'priority' => 1,
        ],
    ],
]
```


Compose the helper in your middleware (or elsewhere), and then use it to
generate URI paths:

```php
use Zend\Expressive\Helper\UrlHelper;

class FooMiddleware
{
    private $helper;

    public function __construct(UrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke($request, $response, callable $next)
    {
        $response = $response->withHeader(
            'Link',
            $this->helper->generate('resource', ['id' => 'sha1'])
        );
        return $next($request, $response);
    }
}
```

You can use the methods `generate()` and `__invoke()` interchangeably (i.e., you
can use the helper as a function if desired). The signature is:

```php
function ($routeName, array $params = []) : string
```

Where:

- `$routeName` is the name of a route defined in the composed router. You may
  omit this argument if you want to generate the path for the currently matched
  request.
- `$params` is an array of substitutions to use for the provided route, with the
  following behavior:
  - If a `RouteResult` is composed in the helper, and the `$routeName` matches
    it, the provided `$params` will be merged with any matched parameters, with
    those provided taking precedence.
  - If a `RouteResult` is not composed, or if the composed result does not match
    the provided `$routeName`, then only the `$params` provided will be used 
    for substitutions.
  - If no `$params` are provided, and the `$routeName` matches the currently
    matched route, then any matched parameters found will be used.
    parameters found will be used.
  - If no `$params` are provided, and the `$routeName` does not match the
    currently matched route, or if no route result is present, then no
    substitutions will be made.

Each method will raise an exception if:

- No `$routeName` is provided, and no `RouteResult` is composed.
- No `$routeName` is provided, a `RouteResult` is composed, but that result
  represents a matching failure.
- The given `$routeName` is not defined in the router.

#### Base Path support

If your application is running under a subdirectory, or if you are running
pipeline middleware that is intercepting on a subpath, the paths generated
by the router may not reflect the *base path*, and thus be invalid. To
accommodate this, the `UrlHelper` supports injection of the base path; when
present, it will be prepended to the path generated by the router.

As an example, perhaps you have middleware running to intercept a language
prefix in the URL; this middleware could then inject the `UrlHelper` with the
detected language, before stripping it off the request URI instance to pass on
to the router:

```php
use Locale;
use Zend\Expressive\Helper\UrlHelper;

class LocaleMiddleware
{
    private $helper;

    public function __construct(UrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke($request, $response, $next)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if (! preg_match('#^/(?P<lang>[a-z]{2})/#', $path, $matches)) {
            return $next($request, $response);
        }

        $lang = $matches['lang'];
        Locale::setDefault($lang);
        $this->helper->setBasePath($lang);

        return $next(
            $request->withUri(
                $uri->withPath(substr($path, 3))
            ),
            $response
        );
    }
}
```

(Note: if the base path injected is not prefixed with `/`, the helper will add
the slash.)

Paths generated by the `UriHelper` from this point forward will have the
detected language prefix.

### ServerUrlHelper

`Zend\Expressive\Helper\ServerUrlHelper` provides the ability to generate a full
URI by passing only the path to the helper; it will then use that path with the
current `Psr\Http\Message\UriInterface` instance provided to it in order to
generate a fully qualified URI.

In order to use the helper, you will need to inject it with the current
`UriInterface` from the request instance. To automate this, we provide
`Zend\Expressive\Helper\ServerUrlMiddleware`, which composes a `ServerUrl`
instance, and, when invoked, injects it with the URI instance.

As such, you will need to:

- Register the `ServerUrlHelper` as a service in your container.
- Register the `ServerUrlMiddleware` as a service in your container.
- Register the `ServerUrlMiddleware` early in your middleware pipeline.

The following examples demonstrate registering the services.

```php
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddlewareFactory;

// zend-servicemanager:
$services->setInvokableClass(ServerUrlHelper::class, ServerUrlHelper::class);
$services->setFactory(ServerUrlMiddleware::class, ServerUrlMiddlewareFactory::class);

// Pimple:
$pimple[ServerUrlHelper::class] = $pimple->share(function ($container) {
    return new ServerUrlHelper();
});
$pimple[ServerUrlMiddleware::class] = $pimple->share(function ($container) {
    $factory = new ServerUrlMiddlewareFactory();
    return $factory($container);
});

// Aura.Di:
$container->set(ServerUrlHelper::class, $container->lazyNew(ServerUrlHelper::class));
$container->set(ServerUrlMiddlewareFactory::class, $container->lazyNew(ServerUrlMiddlewareFactory::class));
$container->set(
    ServerUrlMiddleware::class,
    $container->lazyGetCall(ServerUrlMiddlewareFactory::class, '__invoke', $container)
);
```

To register the `ServerUrlMiddleware` in your middleware pipeline:

```php
use Zend\Expressive\Helper\ServerUrlMiddleware;

// Do this early, before piping other middleware or routes:
$app->pipe(ServerUrlMiddleware::class);

/* ... */
$app->pipeRoutingMiddleware();
$app->pipeDispatchMiddleware();

// Or use configuration:
// [
//     'middleware_pipeline' => [
//         [
//             'middleware' => ServerUrlMiddleware::class,
//             'priority' => PHP_INT_MAX,
//         ],
//     ],
// ]
```

The following dependency configuration will work for all three when using the
Expressive skeleton:

```php
return [
    'dependencies' => [
        'invokables' => [
            ServerUrlHelper::class => ServerUrlHelper::class,
        ],
        'factories' => [
            ServerUrlMiddleware::class => ServerUrlMiddlewareFactory::class,
        ],
    ],
    'middleware_pipeline' => [
        [
            'middleware' => ServerUrlMiddleware::class,
            'priority' => PHP_INT_MAX,
        ],
    ],
]
```

Compose the helper in your middleware (or elsewhere), and then use it to
generate URI paths:

```php
use Zend\Expressive\Helper\ServerUrlHelper;

class FooMiddleware
{
    private $helper;

    public function __construct(ServerUrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke($request, $response, callable $next)
    {
        $response = $response->withHeader(
            'Link',
            $this->helper->generate() . '; rel="self"'
        );
        return $next($request, $response);
    }
}
```

You can use the methods `generate()` and `__invoke()` interchangeably (i.e., you
can use the helper as a function if desired). The signature is:

```php
function ($path = null) : string
```

Where:

- `$path`, when provided, can be a string path to use to generate a URI.

### BodyParams middleware

One aspect of PSR-7 is that it allows you to parse the raw request body, and
then create a new instance with the results of parsing that later processes can
fetch via `getParsedBody()`. It does not provide any actual facilities for
parsing, which means you must write middleware to do so.

This package provides such facilities via `Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware`.
By default, this middleware will detect the following content types:

- `application/x-www-form-urlencoded` (standard web-based forms, without file
  uploads)
- `application/json`, `application/*+json` (JSON payloads)

You can register it manually:

```php
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;

$app->pipe(BodyParamsMiddleware::class);
```

or, if using Expressive, as pipeline middleware:

```php
// config/autoload/middleware-pipeline.global.php
use Zend\Expressive\Helper;

return [
    'dependencies' => [
        'invokables' => [
            Helper\BodyParams\BodyParamsMiddleware::class => Helper\BodyParams\BodyParamsMiddleware::class,
            /* ... */
        ],
        'factories' => [
            /* ... */
        ],
    ],
    'middleware_pipeline' => [
        [
            'middleware' => Helper\BodyParams\BodyParamsMiddleware::class,
            'priority' => 1000,
        ],
        'routing' => [
            /* ... */
        ],
    ],
];
```

#### Strategies

If you want to intercept and parse other payload types, you can add *strategies*
to the middleware. Strategies implement `Zend\Expressive\Helper\BodyParams\StrategyInterface`:

```php
namespace Zend\Expressive\Helper\BodyParams;

use Psr\Http\Message\ServerRequestInterface;

interface StrategyInterface
{
    /**
     * Match the content type to the strategy criteria.
     *
     * @param string $contentType
     * @return bool Whether or not the strategy matches.
     */
    public function match($contentType);

    /**
     * Parse the body content and return a new response.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request);
}
```

You then register them with the middleware using the `addStrategy()` method:

```php
$bodyParams->addStrategy(new MyCustomBodyParamsStrategy());
```

To automate the registration, we recommend writing a factory for the
`BodyParamsMiddleware`, and replacing the `invokables` registration with a
registration in the `factories` section of the `middleware-pipeline.config.php`
file:

```php
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;

class MyCustomBodyParamsStrategyFactory
{
    public function __invoke($container)
    {
        $bodyParams = new BodyParamsMiddleware();
        $bodyParams->addStrategy(new MyCustomBodyParamsStrategy());
        return $bodyParams;
    }
}

// In config/autoload/middleware-pipeline.config.php:
use Zend\Expressive\Helper;

return [
    'dependencies' => [
        'invokables' => [
            // Remove this line:
            Helper\BodyParams\BodyParamsMiddleware::class => Helper\BodyParams\BodyParamsMiddleware::class,
            /* ... */
        ],
        'factories' => [
            // Add this line:
            Helper\BodyParams\BodyParamsMiddleware::class => MyCustomBodyParamsStrategy::class,
            /* ... */
        ],
    ],
];
```

#### Removing the default strategies

If you do not want to use the default strategies (form data and JSON), you can
clear them from the middleware using `clearStrategies()`:

```php
$bodyParamsMiddleware->clearStrategies();
```

Note: if you do this, **all** strategies will be removed! As such, we recommend
doing this only immediately before registering any custom strategies you might
be using.

## Documentation

See the [zend-expressive](https://github.com/zendframework/zend-expressive/blob/master/doc/book)
documentation tree, or browse online at http://zend-expressive.rtfd.org.
