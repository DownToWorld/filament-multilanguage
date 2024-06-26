# WIP! Laravel Filament Automatic Translations

[![Latest Version on Packagist](https://img.shields.io/packagist/v/downtoworld/filament-multilanguage.svg?style=flat-square)](https://packagist.org/packages/downtoworld/filament-multilanguage)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/downtoworld/filament-multilanguage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/downtoworld/filament-multilanguage/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/downtoworld/filament-multilanguage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/downtoworld/filament-multilanguage/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/downtoworld/filament-multilanguage.svg?style=flat-square)](https://packagist.org/packages/downtoworld/filament-multilanguage)

This package aims to auto-discover most of the places that should be translated from your Filament application. 

Then it also lets you translate them directly from the UI while using a cache driver so there is no performance issues.

## Installation

You can install the package via composer:

```bash
composer require "downtoworld/filament-multilanguage:dev-main"
```

You can add the plugin to your panel (normally AdminPanelProvider):

```bash
use DTW\FilamentMultilanguage\FilamentMultilanguagePlugin;

$panel
    ...
    ->plugin(new FilamentMultilanguagePlugin)
    ...
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-multilanguage-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-multilanguage-config"
```

This is the contents of the published config file:

```php
return [
    'languages' => ['en', 'es', 'fr'], // CONFIGURE YOUR AVAILABLE LOCALES HERE
    'authorized_emails' => [
        //user@user.com INSERT YOUR EMAILS HERE
    ]
];
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Sergio Rodenas](https://github.com/sergiorodenas)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
