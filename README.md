![Cover](https://filament.ams3.digitaloceanspaces.com/521/tFyehKIVV346TS3Emhqj5vhGYbFGbW-metaVW50aXRsZWQtMS0wMS5qcGc=-.jpg)

## Filament Modular <a name="realmrhex-modular"></a>

### Introduction <a name="introduction"></a>

Filament Modular is a Laravel package that enables the use of FilamentPHP in the nwidart/laravel-modules Modular structure.
With this package, developers can leverage the power of Filament's admin panel and dashboard components while building modular Laravel applications.

Filament Modular achieves this by providing Laravel Artisan commands that generate the necessary files and folder structure to integrate Filament into a Laravel module.
By using these commands, developers can quickly and easily set up a modular Laravel application with a fully-functional Filament dashboard.

### Installation <a name="installation"></a>

To install Filament Modular, simply add it to your Laravel application using Composer:

```shell
composer require realmrhex/filament-modular
```

#### Laravel Auto-Discovery

Filament Modular is fully compatible with Laravel's package auto-discovery feature, which was introduced in Laravel 5.5. This feature allows packages to be registered with Laravel automatically, without the need for manual registration in the config/app.php file.

With package auto-discovery, Filament Modular can be installed and integrated with a Laravel application quickly and easily. When you install Filament Modular, Laravel will automatically detect the package and register its service provider.

The service provider for Filament Modular is responsible for registering the package's commands, event listeners, and other components with Laravel. Once the service provider is registered, you can use the package's features and functionalities without any additional configuration.

To take advantage of Laravel's package auto-discovery feature, you must be using Laravel 5.5 or higher. If you are using an older version of Laravel, you will need to register the Filament Modular service provider manually in your config/app.php file.

In summary, Filament Modular's support for Laravel's package auto-discovery feature makes it easy for developers to install and integrate the package with a Laravel application. With no additional configuration required, developers can start using Filament Modular's features and functionalities immediately after installation.

### Configuration

Filament Modular comes with a default configuration file that defines the default paths and settings for FilamentPHP integration with Laravel modules.
If you need to customize these settings, you can publish the configuration file using the `vendor:publish` Artisan command:
```shell
php artisan vendor:publish --tag=filament-modular-config
```

### Usage <a name="usage"></a>

You can use all available Filament `make-commands` with `module:` prefix as well with this format.

```shell
php artisan module:make-filament-[command] [resource?] [module]
```

#### Commands

Here is the list of the available commands

![snap](https://raw.githubusercontent.com/RealMrHex/filament-modular/master/snap.png)

#### Options
  - -h, --help to Display this help message
  - -f, --force to Force overwrite of existing files

#### Examples

To create a `Activation` **page** in `User` Module

```shell
php artisan module:make-filament-page Activation User
```

To create a `User` **resource** in `User` module
```shell
php artisan module:make-filament-resource UserResource User
```

### Conclusion

With Filament Modular, developers can enjoy the benefits of both FilamentPHP and the nwidart/laravel-modules Modular structure in their Laravel applications. This package provides an easy-to-use solution for integrating Filament into a modular Laravel application, allowing developers to build powerful and flexible admin panels and dashboards with ease.

## Extra Details <a name="realmrhex-modular-extras"></a>

### Contributing <a name="contributing"></a>

We welcome and appreciate contributions from the community!

If you would like to contribute to Filament Modular, please follow these steps:

1. Fork the repository and create a new branch for your changes.
2. Make your changes and ensure that all tests pass.
3. Submit a pull request with a clear description of your changes and why they are necessary.

Please note that by contributing to this project, you agree to abide by the [Code of Conduct](https://github.com/RealMrHex/filament-modular/blob/master/CODE_OF_CONDUCT.md). If you have any questions or concerns, please feel free to reach out to us or open an issue on our [GitHub repository](https://github.com/RealMrHex/filament-modular).

We appreciate all contributions, big and small, and thank you for helping to make Filament Modular a better tool for everyone!


### Security Vulnerabilities <a name="security"></a>

If you discover a security vulnerability within our product, please send an email to our security team at [RealMrHex@gmail.com](mailto:RealMrHex@gmail.com).
We take all security vulnerabilities seriously and will respond to reports as quickly as possible. We ask that you do not publicly disclose the issue until we have had a chance to address it.

We appreciate your help in making our product more secure.

### Credits <a name="credits"></a>
Filament Modular is developed and maintained by [Armin Hooshmand](https://github.com/RealMrHex).

We would like to thank the following individuals for their contributions to this project:

- [Mohaphez](https://github.com/mohaphez) for their help with testing and bug reports.

We would also like to thank the following projects for their inspiration and guidance:

- [FilamentPHP](https://filamentphp.com/)
- [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules)

If you would like to contribute to Filament Modular or report any issues you encounter, please visit our [GitHub repository](https://github.com/RealMrHex/Filament-Modular).

### License <a name="license"></a>

Filament Modular is open-sourced software licensed under the [MIT license](https://github.com/RealMrHex/filament-modular/blob/master/LICENCE). This means that you are free to use, modify, and distribute the software as you see fit, as long as you include the original license file in your distribution and give appropriate attribution to the original authors.

By using Filament Modular, you agree to abide by the terms and conditions of the MIT license. If you have any questions or concerns about the license, please refer to the [full text of the license](https://opensource.org/license/mit/) for more information.
