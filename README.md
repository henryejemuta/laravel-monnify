# Laravel Monnify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/henryejemuta/laravel-monnify.svg?style=flat-square)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Build Status](https://img.shields.io/travis/henryejemuta/laravel-monnify/master.svg?style=flat-square)](https://travis-ci.org/henryejemuta/laravel-monnify)
[![Quality Score](https://img.shields.io/scrutinizer/g/henryejemuta/laravel-monnify.svg?style=flat-square)](https://scrutinizer-ci.com/g/henryejemuta/laravel-monnify)
[![Total Downloads](https://img.shields.io/packagist/dt/henryejemuta/laravel-monnify.svg?style=flat-square)](https://packagist.org/packages/henryejemuta/laravel-monnify)

A laravel package to seamlessly integrate monnify api within your laravel application

## What is Monnify
Monnify is a leading payment technology that powers seamless transactions for businesses through omnichannel platforms

Create a Monnify Account [Sign Up](https://app.monnify.com/create-account).

Look up Monnify API Documentation [API Documentation](https://docs.teamapt.com/display/MON/Monnify).

## Installation

You can install the package via composer:

```bash
composer require henryejemuta/laravel-monnify
```

Publish Monnify configuration file as well as set default details in .env file:

```bash
php artisan monnify:init
```

## Usage

``` php
// Usage description incomplete
//Import Monnify Facade for use within your application
use HenryEjemuta\LaravelMonnify\Facades\Monnify;

//
$responseBody = Monnify::createSubAccount('058', '0221097794', 'johndoe@example.com');
$responseBody = Monnify::deleteSubAccount('MFY_SUB_683712752381');
$responseBody = Monnify::getSubAccounts();
$responseBody = Monnify::getBanksWithUSSDShortCode();
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email henry.ejemuta@gmail.com instead of using the issue tracker.

## Credits

- [Henry Ejemuta](https://github.com/henryejemuta)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
