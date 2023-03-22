Active for Laravel
======

[![Travis](https://app.travis-ci.com/sebastienheyd/active.svg?branch=master)](https://app.travis-ci.com/github/sebastienheyd/active)
[![Laravel](https://img.shields.io/badge/Laravel-6.x%20→%2010.x-green?logo=Laravel&style=flat-square)](https://laravel.com/)
[![License](https://poser.pugx.org/hieu-le/active/license.svg)](LICENSE)

Helper class for Laravel to get the active class based on the current url.

This project is based on [hieu-le/active](https://github.com/letrunghieu/active)

## Installation

Require this package as your dependencies:

```
composer require sebastienheyd/active
```

## Configuration (optional)

You can define another default class name instead of `active` by editing the `active.php` configuration file.

To publish the configuration file you can use the following command:

```
php artisan vendor:publish --tag=active
```

## Usage

* Use the alias: `Active::getClassIf($condition, $activeClass = 'active', $inactiveClass = '')`
* Use the application container: `app('active')->getClassIf($condition, $activeClass = 'active', $inactiveClass = '')`
* Use the helper function: `active_class($condition, $activeClass = 'active', $inactiveClass = '')`

Explanation: if `$condition` is true, the value of `$activeClass` is returned, otherwise the value of `$inactiveClass` is returned.

```php
active_class(true); // 'active' (default value set in the configuration file)
active_class(false); // ''
active_class(if_uri([$currentUri]), 'selected'); // 'selected'
active_class(if_uri_pattern([$pattern1, $pattern2]), 'active', 'other'); // 'other'
```

### Check the current URI

All of checking methods return boolean result (`true` or `false`). You can use the result in the condition of `active_class` or write your own expression.

### Check the whole URI

Usage:

* Use the alias: `Active::checkUri(array $uris)`
* Use the application container: `app('active')->checkUri(array $uris)`
* Use the helper function: `if_uri(array $uris)`

Explanation: you give an array of URI, the package will return true if the current URI is in your array. Remember that an URI does not begin with the slash (`/`) except the root.

### Check the URI with some patterns

Usage:

* Use the alias: `Active::checkUriPattern(array $patterns)`
* Use the application container: `app('active')->checkUriPattern(array $patterns)`
* Use the helper function: `if_uri_pattern(array $patterns)`

Explanation: you give an array of patterns, the package will return true if the current URI matches one of the given pattern. Asterisks may be used in the patterns to indicate wildcards.

### Check the query string

Usage:

* Use the alias: `Active::checkQuery($key, $value)`
* Use the application container: `app('active')->checkQuery($key, $value)`
* Use the helper function: `if_query($key, $value)`

Explanation: the package will return true if one of the following condition is true:

* The current query string contains a parameter named `$key` with any value and the value of `$value` is `false`.
* The current query string does not contain a parameter named `$key` and the value of `$value` is `null`.
* The current query string contains a parameter named `$key` whose value is a string equals to `$value`.
* The current query string contains a parameter named `$key` whose value is an array that contain the `$value`.

```php
// the current query string is ?x=1&y[0]=a&y[1]=b

if_query('x', null); // true
if_query('x', 1); // true
if_query('x', 2); // false
if_query('y', 'a'); // true
if_query('y', 'c'); // false
if_query('z', null); // false
```

## Check the current route

### Check the exact route name

Usage:

* Use the alias: `Active::checkRoute(array $routes)`
* Use the application container: `app('active')->checkRoute(array $routes)`
* Use the helper function: `if_route(array $routes)`

Explanation: you give an array of route names, the package will return true if the name of the current route (which can be null) is in your array.

### Check the route name with some patterns

Usage:

* Use the alias: `Active::checkRoutePattern(array $patterns)`
* Use the application container: `app('active')->checkRoutePattern(array $patterns)`
* Use the helper function: `if_route_pattern(array $patterns)`

Explanation: you give an array of patterns, the package will return true if the name of the current route (which can be null) matches one of the given pattern. Asterisks may be used in the patterns to indicate wildcards.

### Check the route parameter value

Usage:

* Use the alias: `Active::checkRouteParam($key, $value)`
* Use the application container: `app('active')->checkRouteParam($key, $value)`
* Use the helper function: `if_route_param($key, $value)`

Explanation: the package will return `true` if one of the following condition is true:

* The current route contains a parameter named `$key` whose value is `$value`.
* The current route does not contain a parameter named `$key` and the value of `$value` is null.

Read more about route parameter in the [Laravel documentation](https://laravel.com/docs/routing#route-parameters).

## Get the current values

### Get the current action

Usage:

* Use the alias: `Active::getAction()`
* Use the application container: `app('active')->getAction()`
* Use the helper function: `current_action()`

Explanation: if the current route is bound to a class method, the result will be a string like `App\Http\Controllers\YourController@yourMethod`. If the route is bound to a closure, the result will be the `Closure` string.

### Get the current controller class

Usage:

* Use the alias: `Active::getController()`
* Use the application container: `app('active')->getController()`
* Use the helper function: `current_controller()`

Explanation: if the current route is bound to a class method, the result will be the full qualified class name of the controller class, like `App\Http\Controllers\YourController`. If the route is bound to a closure, the result will be the `Closure` string.

### Get the current controller method

Usage:

* Use the alias: `Active::getMethod()`
* Use the application container: `app('active')->getMethod()`
* Use the helper function: `current_method()`

Explanation: if the current route is bound to a class method, the result will be the name of the controller method. like `yourMethod`. If the route is bound to a closure, the result will be the empty string.

## Example

The example below illustrate the usage of this package in a sidebar with [Bootstrap list group](https://getbootstrap.com/docs/5.3/components/list-group/) :

```html
<ul class="list-group">
    <a href="" class="list-group-item {{ active_class(if_route('users.list') && if_query('active', 1)) }}">
        Active users
    </a>
    <a href="#" class="list-group-item {{ active_class(if_route('users.list') && if_query('active', 0)) }}">
        Inactive users
    </a>
    <a href="#" class="list-group-item  {{ active_class(if_action('App\Http\Controllers\UserController@getNewUser')) }}">
        Add users
    </a>
</div>
```