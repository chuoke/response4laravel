# A API response helper for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chuoke/response4laravel.svg?style=flat-square)](https://packagist.org/packages/chuoke/response4laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/chuoke/response4laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/chuoke/response4laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/chuoke/response4laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/chuoke/response4laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/chuoke/response4laravel.svg?style=flat-square)](https://packagist.org/packages/chuoke/response4laravel)

This Laravel package can halp organize uniform response data format for API. It is designed to be able to parse all types of data, including API Resource, Collection, and Paginator. And you can custom it.

## Installation

You can install the package via composer:

```bash
composer require chuoke/response4laravel
```

## Usage

### Import response object

To enable uniform response, we typically define an `BaseController` and create a helper method for uniform response.

First, in the `BaseController` use the helper trait, there is a `response` method that returns the `Chuoke\Response4Laravel\Response` instance object.

By the way, more helper methods will be added in the future, depending on the situation.

```php
use Chuoke\Response4Laravel\Concerns\ResponseHelper;
```

Or make a helper method you want. In this way, it is easy to customize the response object parameters, as detailed examples are shown later.

```php
use Chuoke\Response4Laravel\Response;

protected function apiResponse() {
    return Response::make();
}
```

Next, you can use it in your controller.

### General Usage

According to the general case, shortcut methods are defined for only the most frequently used states.

```php
$this->response()->ok(); // or $this->response()->ok($data)
$this->response()->created($data, 'The data was created successfule!');
$this->response()->notFound('The data not exists');
$this->response()->badRequest('Your submit is wrong.');
```

See the code for more details, create custom response classes if they are missing what you need, or please submit a PR if there are more general methods that need to be added.

### Response Resource

##### Single resource

```php
$this->response()->ok($userResource);
// output content: {"data":{"id":1,"name":"name"}}
```

By default, the `data` will not exist if the resource dont use wrap.

##### Collection resource

```php
$this->response()->ok($userCollectionResource);
// output content: {"data":[{"id":1,"name":"name"},{"id":2,"name":"name2"}]}
```

The `data` always exists.

And you can customize the `data` key.

```php
$this->response()->collectionWrap('users')->ok($userCollectionResource);
// output content: {"users":[{"id":1,"name":"name"},{"id":2,"name":"name2"}]}
```

> The data wrapper uses first value defined in the response class, and then uses of resource. If neither is defined, then the single resource will not wrapped, but the collection is always wrapped. The default key name is `data`, can set diffrent value for single data and collection.

### Paging Response

```php
$users = User::query()->paginate($request->get('per_page'));

return $this->ok(UserResource::collection($users));
// output content: {"data":[{"id":1,"name":"user1"},{"id":2,"name":"user2"},{"id":3,"name":"user3"}],"links":{"first":"http:\/\/localhost\/paginator?page=1","last":"http:\/\/localhost\/paginator?page=4","prev":null,"next":"http:\/\/localhost\/paginator?page=2"},"meta":{"current_page":1,"from":1,"last_page":4,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http:\/\/localhost\/paginator?page=1","label":"1","active":true},{"url":"http:\/\/localhost\/paginator?page=2","label":"2","active":false},{"url":"http:\/\/localhost\/paginator?page=3","label":"3","active":false},{"url":"http:\/\/localhost\/paginator?page=4","label":"4","active":false},{"url":"http:\/\/localhost\/paginator?page=2","label":"Next &raquo;","active":false}],"path":"http:\/\/localhost\/paginator","per_page":3,"to":3,"total":10}}
```

### Customize Response

In many cases, we're used to having a consistent format for our response data.

```php
protected function response()
{
    return Response::make()
        ->responseUsing(function ($data, $status, $message) {
            return response()->json([
                'code' => $status,
                'message' => $message ?: 'success',
                'data' => is_array($data) && array_key_exists('data', $data) && count($data) === 1 ? $data['data'] : $data,
            ], 200);
        });
}

//
$this->response()->ok(new UserResource(User::first()));
// output content: {"code":200,"message":"success","data":{"id":1,"name":"user1"}}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [chuoke](https://github.com/chuoke)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
