# Laravel Visual Console

[![Latest Version on Packagist](https://img.shields.io/packagist/v/toneflix-code/laravel-visualconsole.svg?style=flat-square)](https://packagist.org/packages/toneflix-code/laravel-visualconsole)
[![Total Downloads](https://img.shields.io/packagist/dt/toneflix-code/laravel-visualconsole.svg?style=flat-square)](https://packagist.org/packages/toneflix-code/laravel-visualconsole)
![GitHub Actions](https://github.com/toneflix-code/laravel-visualconsole/actions/workflows/main.yml/badge.svg)

Laravel Visual Console is designed to give you a visual experince of your most frequent Laravel proccesses and artisan commands, and system management.

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

This package depends on google drive for backup storage, although you can use any storage your spp is configured to run with.

#### Please follow [Google Docs](https://developers.google.com/drive/v3/web/enable-sdk) to obtain your `client ID, client secret & refresh token` which is required to get the system running.

#### In addition you can also check these easy-to-follow tutorial by [@ivanvermeyen](https://github.com/ivanvermeyen/laravel-google-drive-demo)

-   [Getting your Client ID and Secret](https://github.com/ivanvermeyen/laravel-google-drive-demo/blob/master/README/1-getting-your-dlient-id-and-secret.md)
-   [Getting your Refresh Token](https://github.com/ivanvermeyen/laravel-google-drive-demo/blob/master/README/2-getting-your-refresh-token.md)

## Usage

After installation, the package is ready to use, simply point your browser to http://youdomainexample.com/system/console/login.

The default setup is configured to work with your current authentication model.

### Authorization

By defautlt the package will check the `privileges` field of the current authentication model `[User]` for if the the authenticating user has the `admin` Privilege assuming the value of the field is a numeric list of attribute/privileges, if it fails to confirm it checks if the value of the field is exactly `admin`, once confirmed the user is authenticated.

If you which to change the behavior you should check the [Post Installation](post-installation) section for how to publish the config and modify the suite your requirements, you can also set the `permission_field` and `permission_value` config values to `null` in order to disable this behaviour. The implicatio of disabling the feature is that anyone with login access can also access the visual console.

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
