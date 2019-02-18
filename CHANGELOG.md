# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 5.1.3 - TBD

### Added

- [#69](https://github.com/zendframework/zend-expressive-helpers/pull/69) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 5.1.2 - 2018-07-26

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#66](https://github.com/zendframework/zend-expressive-helpers/pull/66) updates the `Content-Type` header matching to be more robust, preventing invalid matches.

## 5.1.1 - 2018-07-25

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#64](https://github.com/zendframework/zend-expressive-helpers/pull/64) prevents an unnecessary `json_decode()` call when the request contains
  no body or an empty body.

## 5.1.0 - 2018-06-05

### Added

- Nothing.

### Changed

- [#62](https://github.com/zendframework/zend-expressive-helpers/pull/62) modifies the `UrlHelperFactory` to allow specifying both a string `$basePath` as well as a string `$routerServiceName`
  to its constructor. This change allows having discrete factory instances for generating helpers
  that use different router instances and/or which operate under path-segregated middleware.

- [#62](https://github.com/zendframework/zend-expressive-helpers/pull/62) modifies the `UrlHelperMiddlewareFactory` to allow specifying a string `$urlHelperServiceName` to its constructor.
  This change allows having discrete factory instances for generating URL helper middleware
  that use different URL helper instances.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 5.0.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 5.0.0 - 2018-03-15

### Added

- [#55](https://github.com/zendframework/zend-expressive-helpers/pull/55)
  adds support for PSR-15 middleware.

- [#57](https://github.com/zendframework/zend-expressive-helpers/pull/57)
  adds `Zend\Expressive\Router\ZendRouter\ConfigProvider` and exposes it as a
  config provider within the package definition.

### Changed

- [#51](https://github.com/zendframework/zend-expressive-helpers/pull/51)
  changes a number of signatures to provide scalar type hints, return type hints,
  and nullable types. Signatures with changes include:

  - `Zend\Expressive\Helper\BodyParams\StrategyInterface`:
    - The `match()` signature changes to `match(string $contentType) : bool`
    - The `parse()` signature changes to `parse(ServerRequestInterface $request) : ServerRequestInterface`
  - `Zend\Expressive\Helper\ServerUrlHelper` updates its public API to read as follows:
    - `__invoke(string $path = null) : string`
    - `generate(string $path = null) : string`
    - `setUri(UriInterface $uri) : void`
  - `Zend\Expressive\Helper\UrlHelper` updates its public API to read as follows:
    - `__invoke(?string $routeName = null, array $routeParams = [], array $queryParams = [], ?string $fragmentIdentifier = null, array $options = []) : string`
    - `generate(?string $routeName = null, array $routeParams = [], array $queryParams = [], ?string $fragmentIdentifier = null, array $options = []) : string`
    - `setRouteResult(RouteResult $result) : void`
    - `setBasePath(string $path) : void`
    - `getRouteResult() : ?RouteResult`
    - `getBasePath() : string`

### Deprecated

- Nothing.

### Removed

- [#50](https://github.com/zendframework/zend-expressive-helpers/pull/50)
  removes support for PHP versions 5.6 and 7.0.

- [#50](https://github.com/zendframework/zend-expressive-helpers/pull/50) and
  [#55](https://github.com/zendframework/zend-expressive-helpers/pull/55)
  remove support for http-interop/http-middleware of all versions.

### Fixed

- Nothing.

## 4.2.0 - 2017-10-09

### Added

- [#46](https://github.com/zendframework/zend-expressive-helpers/pull/46) adds
  support for http-interop/http-middleware 0.5.0 via a polyfill provided by the
  package webimpress/http-middleware-compatibility. Essentially, this means you
  can drop this package into an application targeting either the 0.4.1 or 0.5.0
  versions of http-middleware, and it will "just work".

### Changed

- [#46](https://github.com/zendframework/zend-expressive-helpers/pull/46)
  updates the minimum supported zend-expressive-router version to 2.2.0.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 4.1.0 - 2017-09-11

### Added

- [#45](https://github.com/zendframework/zend-expressive-helpers/pull/45) adds
  `Zend\Expressive\Helper\ContentLengthMiddleware`. This middleware will inject
  a `Content-Length` response header if none already exists and the response
  body size is non-null.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 4.0.1 - 2017-09-11

### Added

- Nothing.

### Changed

- We no longer test against HHVM. Tests were running against that platform prior
  to this release, but we are no longer testing against it as the PHP versions
  we support have features that HHVM does not support at this time.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#42](https://github.com/zendframework/zend-expressive-helpers/pull/42) fixes
  how the `UrlHelper` generates a URI when using the currently matched route.
  Previously, doing so would not append either provided query string arguments
  or fragment identifiers; it now does.

## 4.0.0 - 2017-03-13

### Added

- Nothing.

### Changed

- [#39](https://github.com/zendframework/zend-expressive-helpers/pull/39)
  switches from container-interop to psr-container for its `ContainerInterface`
  usage. This is a breaking change for anybody extending any of the factories,
  as the typehints will now be different (`Psr\Container\ContainerInterface`
  versus `Interop\Container\ContainerInterface`).

- [#40](https://github.com/zendframework/zend-expressive-helpers/pull/40)
  switches all middleware from invokable, double-pass to instead implement
  http-interop/http-middleware. This means that any extensions of middleware
  contained in this package will need to be updated; it also means that the
  middleware can now only be used in systems that support
  http-interop/http-middleware.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.1 - 2017-01-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#34](https://github.com/zendframework/zend-expressive-helpers/pull/34) fixes
  the signature of `UrlHelper::generate()` to match that of
  `UrlHelper::__invoke()`, and, more specifically, ensuring that the default
  `$fragment` value is `null` and not `''`, fixing a subtle issue when calling
  `generate()` without a `$fragment` value.

## 3.0.0 - 2016-01-11

### Added

- [#23](https://github.com/zendframework/zend-expressive-helpers/pull/23) adds
  support to `UrlHelper` for generating a URI based on the currently matched
  route and parameters.

- [#9](https://github.com/zendframework/zend-expressive-helpers/pull/9) updates
  `UrlHelper` to pass `$routerOptions` to the underlying router, if provided.

  - **BREAKING CHANGE:** This change adds an _optional_ `$options` parameter to
    the `UrlHelper::__invoke()` and `UrlHelper::generate()` methods. Users who
    have extended this class **MUST** update the method signatures accordingly
    to avoid a PHP Fatal Error. If you have not extended this class, no further
    action is required for compatibility.

- [#27](https://github.com/zendframework/zend-expressive-helpers/pull/27) adds
  query string argument and fragment identifier support to `UrlHelper`.

  - **BREAKING CHANGE:** This change adds _optional_ `$routeParams`,
    `$queryParams`, and `$fragmentIdentifier` parameters to the
    `UrlHelper::__invoke()` and `UrlHelper::generate()` methods, in addition to
    the aforementioned `$options` parameter. Users who have extended this class
    **MUST** update the method signatures accordingly to avoid a PHP Fatal
    Error. If you have not extended this class, no further action is required
    for compatibility.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.2.0 - 2016-12-23

### Added

- Nothing.

### Changes

- [#30](https://github.com/zendframework/zend-expressive-helpers/pull/30) Use
  new ZF coding standard
- [#31](https://github.com/zendframework/zend-expressive-helpers/pull/31) Check
  to ensure 100% test coverage is retained

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.1 - 2016-12-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#29](https://github.com/zendframework/zend-expressive-helpers/pull/29) Don't throw exception on empty JSON body

## 2.1.0 - 2016-10-02

### Added

- [#19](https://github.com/zendframework/zend-expressive-helpers/pull/19) FormUrlEncodedStrategy parses raw
  request bodies, if needed.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#22](https://github.com/zendframework/zend-expressive-helpers/pull/22) updates JsonStrategy test suite to
  function with both the `json` and `jsonc` extensions

## 2.0.3 - 2016-09-01

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#21](https://github.com/zendframework/zend-expressive-helpers/pull/21) Respond with 400 on bad JSON input

## 2.0.2 - 2016-08-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/zendframework/zend-expressive-helpers/pull/15) URLs
  generated by `UrlHelper` will always include the `$basePath` if one is set.

## 2.0.1 - 2016-08-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#18](https://github.com/zendframework/zend-expressive-helpers/pull/18) parsing
  request body with the `JsonStrategy` will implicitly rewind the stream in order
  to parse the entire body, rather than just parsing the remaining contents.

## 2.0.0 - 2017-01-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#7](https://github.com/zendframework/zend-expressive-helpers/pull/7) removes
  the `RouteResultObserverInterface` implementation from `UrlHelper`. This also
  means that the `UrlHelperMiddleware` no longer registers the `UrlHelper` as a
  route result observer, but instead just injects it with the `RouteResult`
  present as a request attribute, if any.

### Fixed

- Nothing.

## 1.4.0 - 2016-01-01

### Added

- [#6](https://github.com/zendframework/zend-expressive-helpers/pull/6) adds base
  path support to the `UrlHelper`. Middleware may now call
  `UrlHelper::setBasePath()` on an instance to set the path prefix to add to all
  URIs generated by the helper, which is often useful when working in an
  environment where the application is in a subdirectory, or where you wish to
  use a version or locale prefix to all paths. You may clear the base path by
  passing an empty string to the `setBasePath()` method.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.0 - 2015-12-22

### Added

- [#3](https://github.com/zendframework/zend-expressive-helpers/pull/3) and
  [#5](https://github.com/zendframework/zend-expressive-helpers/pull/5) add
  a new `Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware` for use in
  parsing the request body. The middleware consumes strategies, which match
  against the `Content-Type` header, and, if matched, parse the body and return
  a new request with the parsed body parameters.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.2 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2015-12-22

### Added

- [#4](https://github.com/zendframework/zend-expressive-helpers/pull/4) adds the
  protected method `getRouteResult()`, providing extending classes access to the
  private `$result` member.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.0 - 2015-12-08

### Added

- [#2](https://github.com/zendframework/zend-expressive-helpers/pull/2) adds the
  following classes:
  - `UrlHelperMiddleware`, which accepts a `UrlHelper` instance and a
    `RouteResultSubjectInterface` instance; during invocation, it attaches the
    helper to the subject as an observer.
  - `UrlHelperMiddlewareFactory`, which creates a `UrlHelperMiddleware` instance
    from the registered `UrlHelper` and `RouteResultSubjectInterface` (or
    `Application`) instances.

### Deprecated

- Nothing.

### Removed

- [#2](https://github.com/zendframework/zend-expressive-helpers/pull/2) removes
  registration of the generated `UrlHelper` with the `Application` instance
  within the `UrlHelperFactory`. This change was made to observed race
  conditions when the `UrlHelper` is created within the context of the
  `ApplicationFactory` (e.g., when generating a `TemplatedErrorHandler`
  instance).

### Fixed

- Nothing.

## 1.1.0 - 2015-12-06

### Added

- [#1](https://github.com/zendframework/zend-expressive-helpers/pull/1) adds a
  dependency on zendframework/zend-expressive-router, which replaces the
  dependency on zendframework/zend-expressive. This change means the component
  can be used without Expressive, and also removes a potential circular
  dependency issue in consumers of the package.

### Deprecated

- Nothing.

### Removed

- [#1](https://github.com/zendframework/zend-expressive-helpers/pull/1) removes
  the zendframework/zend-expressive, replacing it with a dependency on
  zendframework/zend-expressive-router.

### Fixed

- Nothing.

## 1.0.0 - 2015-12-04

Initial release.

### Added

- `Zend\Expressive\Helper\UrlHelper` provides the ability to generate a URI path
  based on a given route defined in the `Zend\Expressive\Router\RouterInterface`.
  If registered as a route result observer, and the route being used was also
  the one matched during routing, you can provide a subset of routing
  parameters, and any not provided will be pulled from those matched.
- `Zend\Expressive\Helper\ServerUrlHelper` provides the ability to generate a
  full URI by passing only the path to the helper; it will then use that path
  with the current `Psr\Http\Message\UriInterface` instance provided to it in
  order to generate a fully qualified URI.
- `Zend\Expressive\Helper\ServerUrlMiddleware` is pipeline middleware that can
  be registered with an application; it will inject a `ServerUrlHelper` instance
  with the URI composed in the provided `ServerRequestInterface` instance.
- The package also provides factories compatible with container-interop that can
  be used to generate instances.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
