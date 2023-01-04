# Laravel Visual Console

[![Latest Version on Packagist](https://img.shields.io/packagist/v/toneflix-code/laravel-visualconsole.svg?style=flat-square)](https://packagist.org/packages/toneflix-code/laravel-visualconsole)
[![Total Downloads](https://img.shields.io/packagist/dt/toneflix-code/laravel-visualconsole.svg?style=flat-square)](https://packagist.org/packages/toneflix-code/laravel-visualconsole)

<!-- ![GitHub Actions](https://github.com/toneflix-code/laravel-visualconsole/actions/workflows/main.yml/badge.svg) -->

Laravel Visual Console is designed to give you a visual experience of your most frequent Laravel processes, artisan commands, and system management. This could come in really handy if you build and manage APIs that don't require you to build any additional user interfaces.
![preview](https://user-images.githubusercontent.com/52163001/210129782-1c701a9f-6de6-4e00-9a9a-bc731d7965c2.png)

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

By default, the package will check the `privileges` field of the current authentication model `[User]` for if the authenticating user has the `admin` Privilege assuming the value of the field is a numeric list of attributes/privileges, if it fails to confirm it checks if the value of the field is exactly `admin`, once confirmed the user is authenticated.

If you which to change the behaviour you should check the [Post Installation](#post-installation) section for how to publish the config and modify it to suit your requirements, you can also set the `permission_field` and `permission_value` config values to `null` in order to disable this behaviour. Disabling the feature implies that anyone with log in access can also access the visual console.

### Custom Commands

The library also exposes a few custom Artisan commands to help you with certain everyday tasks, even though these commandsa are accessible through the UI, they are still Artisan commands and are as much accessible through your terminal.

1. `system:deploy`: Automatically deploys the latest code from the git repository associated with your project.
   Before you run this command, make sure you have set up a git repository and have added a remote named "origin".

    - ARGUMENTS

        1. `--branch=`: The branch to deploy, the default is `main`.
        2. `--force`: Force the deployment.
        3. `--dev`: Run in development mode (This will prevent composer from removing dev dependencies)
        4. `--log-level=[level]`: How log the output should handled. `0` = none, `1` = console only, `2` = file and console.
        5. `--mock-php`: If your server is on a shared hosting which uses a different version on the CLI less that php 8.1, this option allows you to use a different version of php of your choice, publish the [config file](#post-installation) and update the `php_bin` option or set `VISUALCONSOLE_PHP_BINARY` option on your .env file (Make sure the path is an abosolute path to your prefered php binary). You can also set the `composer` option or set the `VISUALCONSOLE_COMPOSER` option on your .env.
        6. `--composer=[command]` Allows you to run a custom composer command, this is useful if you want to run a composer command before the deployment. E.g. `--composer="install --no-dev"`. For now this option only supports the `install` and `update` commands only.
        7. `--ensure-commit`: Make sure there are no uncommitted changes before deployment.

    Example:

    ```php
    php artisan system:deploy --branch=main
    ```

2. `system:control`: Helps you perforom common system tasks like backup, backup restore and system reset.

    - PARAMETERS

        1. `action` The specific action to carryout [reset, backup, restore]

    - ARGUMENTS

    1. `--w|wizard`: Let the wizard guide you through the whole process.
    2. `--r|restore`: Restore the system to the last backup or provide the `--signature` option to restore to a known backup signature.
    3. `--s|signature=`: Set the backup signature value to restore a particular known backup. E.g. `2022-04-26_16-05-34`.
    4. `--b|backup`: During system reset, do a complete system backup before the reset.
    5. `--d|delete`: If the restore option is set, this option will delete the backup files after successfull a restore.
    6. `--f|force`: Force the action to execute.

    Example:

    ```php
    php artisan system:control backup --w|wizard
    ```

3. `system:key-gen`: Generate a webhook secret key for the application, you can use this key for authorizing github or any other services where you need access tho the artisan webhook interface.

    Example:

    ```php
    php artisan system:key-gen
    ```

### Artisan Webhook Interface

The Artisan Webhook Interface allows you to remotely run any of the given artisan commands listed above where user authentication is not possible.
The interface can be accessed like this: `http://youdomainexample.com/system/webhooks/artisan/[command [--param1] [--param2]]`.
When accessing this endpoint, you will have to pass the HMAC hex digest of the request body, generated using the SHA-1 or SHA-256 hash function and the secret as the HMAC key through the `X-Hub-Signature` header for Github or the `X-Signature` for other services, provided for convinience purpose.

### Queues and Failed Jobs

The library currently supports the database connection for queued jobs.

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email code@toneflix.com.ng instead of using the issue tracker.

## Credits

-   [Toneflix Code](https://github.com/toneflix-code)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
