# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
