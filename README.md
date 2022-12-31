# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/toneflix-code/laravel-visualconsole.svg?style=flat-square)](https://packagist.org/packages/toneflix-code/laravel-visualconsole)
[![Total Downloads](https://img.shields.io/packagist/dt/toneflix-code/laravel-visualconsole.svg?style=flat-square)](https://packagist.org/packages/toneflix-code/laravel-visualconsole)
![GitHub Actions](https://github.com/toneflix-code/laravel-visualconsole/actions/workflows/main.yml/badge.svg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require toneflix-code/laravel-visualconsole
```

## Post Installation

After installation you must publish the package assets by running:

```bash
php artisan vendor:publish --tag=visualconsole-assets
```

Optionally you can publish the configuration file by running:

```bash
php artisan vendor:publish --tag=visualconsole-config
```

You can also optionally publish the views by running:

```bash
php artisan vendor:publish --tag=visualconsole-view
```

If needed, you can also publish the routes by running:

```bash
php artisan vendor:publish --tag=visualconsole-routes
```

## Getting Google Keys

#### Please follow [Google Docs](https://developers.google.com/drive/v3/web/enable-sdk) to obtain your `client ID, client secret & refresh token`.

#### In addition you can also check these easy-to-follow tutorial by [@ivanvermeyen](https://github.com/ivanvermeyen/laravel-google-drive-demo)

-   [Getting your Client ID and Secret](https://github.com/ivanvermeyen/laravel-google-drive-demo/blob/master/README/1-getting-your-dlient-id-and-secret.md)
-   [Getting your Refresh Token](https://github.com/ivanvermeyen/laravel-google-drive-demo/blob/master/README/2-getting-your-refresh-token.md)

## Usage

```php
// Usage description here
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email code@toneflix.com.ng instead of using the issue tracker.

## Credits

-   [Toneflix Code](https://github.com/toneflix-code)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
