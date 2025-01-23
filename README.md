# Annotation Laroute

Using PHP 8 attributes to automatically generate routes in order to streamline Laravel routing, enhancing developer productivity while maintaining full compatibility with Laravel's routing functionality.

[![GitHub Tag](https://img.shields.io/github/v/tag/dependencies-packagist/annotation-laroute)](https://github.com/dependencies-packagist/annotation-laroute/tags)
[![Total Downloads](https://img.shields.io/packagist/dt/annotation/laroute?style=flat-square)](https://packagist.org/packages/annotation/laroute)
[![Packagist Version](https://img.shields.io/packagist/v/annotation/laroute)](https://packagist.org/packages/annotation/laroute)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/annotation/laroute)](https://github.com/dependencies-packagist/annotation-laroute)
[![Packagist License](https://img.shields.io/github/license/dependencies-packagist/annotation-laroute)](https://github.com/dependencies-packagist/annotation-laroute)

## Installation

You can install the package via [Composer](https://getcomposer.org/):

```bash
composer require annotation/laroute
```

## Usage

### Directories Configurations

#### No Configuration

```php
'directories' => [
    app_path('Http/Controllers/Backend'),
],
```

#### Basic Configuration

```php
'directories' => [
     app_path('Http/Controllers/Backend')    => [
         'domain'     => env('LAROUTE_DOMAINS_BACKEND', 'backend.lvh.me'),
         'prefix'     => 'backend',
         'as'         => 'backend.',
         'middleware' => 'web',
         // Only routes matching the pattern in the file are registered
         'only'       => ['*Controller.php'],
         // Except routes from registration pattern matching file
         'except'     => [],
     ],
],
```

#### Multiple Configurations

```php
'directories' => [
     app_path('Http/Controllers/Enterprise') => [
         [
             'domain'     => env('LAROUTE_DOMAINS_ENTERPRISE', 'enterprise.lvh.me'),
             'prefix'     => 'enterprise',
             'as'         => 'enterprise.',
             'middleware' => [
                 'web',
                 'auth',
             ],
             // Only routes matching the pattern in the file are registered
             'only'       => ['*Controller.php'],
             // Except routes from registration pattern matching file
             'except'     => ['AccountController.php'],
         ], [
             'domain'     => env('LAROUTE_DOMAINS_ENTERPRISE', 'enterprise.lvh.me'),
             'prefix'     => 'enterprise',
             'as'         => 'enterprise.',
             'middleware' => [
                 'web',
             ],
             // Only routes matching the pattern in the file are registered
             'only'       => ['AccountController.php'],
             // Except routes from registration pattern matching file
             'except'     => [],
         ],
     ],
],
```

### Route Discover

#### Basic usage

```php
use Annotation\Routing\Facades\Route;

Route::discover();
```

#### Route discover monitoring

```php
use Annotation\Routing\Facades\Route;

Route::discover(function (array $data, \Closure $next) {
    return $next($data);
});
```

### Gateway Routing

```php
use Annotation\Routing\Facades\Route;

Route::gateWay('gateway.do', function (Request $request) {
    return $request->get('action');
});
```

> Specify the route to get the named source

```php
use Annotation\Routing\Facades\Route;

Route::gateWay('gateway.do', function (Request $request) {
    return $request->get('action');
}, function (Request $request) {
    return $request->get('version');
});
```

> Specify the route to obtain the version source

## License

Nacosvel Contracts is made available under the MIT License (MIT). Please see [License File](LICENSE) for more information.
