# Joomla! Framework Site

This application is the code powering the [Joomla! Framework](https://framework.joomla.org) website.

## Requirements

* PHP 7.0+
    * PDO with MySQL support
* Composer
* Apache with mod_rewrite enabled
* If changing web assets, NPM is required

## Installation

1. Clone this repo on your web server
2. Run the `composer install` command to install all dependencies
3. Copy `etc/config.dist.json` to `etc/config.json` and configure your environment
4. Run `vendor/bin/phinx migrate` to set up the database
5. Run `npm install` to install dependencies
6. Run `npm run prod` to compile assets (SCSS, JS) for production

## Modifying Web Assets

The web assets (CSS, JavaScript, and images) are compiled and processed with [Laravel Mix](https://github.com/JeffreyWay/laravel-mix) which is a wrapper around webpack. The primary source for all assets is the `assets` directory, running Mix will place production assets into the `www/media` directory.

The use of Mix requires [NPM](https://www.npmjs.com/) as hinted at in the Installation section.  NPM 5+ and Node 8+ are suggested.  Three scripts are available:

* `npm run prod` will compile assets for production, which includes compression and minification; code must be committed to this repository with the production configuration
* `npm run dev` will compile the assets without production optimizations
* `npm run watch` is the same as the `dev` script but with the `--watch` flag passed

## Database Schema

The database schema is managed through [Phinx](https://phinx.org/).  The `phinx.php` file at the root of this repo configures the Phinx environment.  Please see their documentation for more information.

## Application Configuration

The application's configuration is defined as follows:

* Database - The `joomla/database` package is used to provide a database connection as required
    * `database.driver` - Defines the active driver, must be `mysql`
    * `database.host` - The address of the database server
    * `database.user` - The user to connect to the database as
    * `database.password` - The password for the database user
    * `database.name` - The name of the database to use
    * `database.prefix` - The prefix to use for the database's tables
* Template - The `twig/twig` package is used for the application's templates
    * `template.debug` - Flag to enable Twig's debug functionality, when enabled the caching functionality is not available
    * `template.cache.enabled` - Flag to enable Twig's caching functionality
    * `template.cache.path` - The path relative to the repo root where cached Twig files should be stored
* Analytics - The API of this site supports sending basic data to Google Analytics
    * `analytics.enabled` - Flag to enable this feature
    * `analytics.account` - The UA code of the Analytics account to send data to
* Router - The `joomla/router` package is used to handle the application's routing
    * `router.cache` - Flag to enable the use of a compiled router file
* Logging - The `monolog/monolog` package is used for logging functionality
    * `log.level` - The default logging level to use for all application loggers, this defaults to the `ERROR` level
    * `log.application` - The logging level to use specifically for the `monolog.handler.application` logger; defaults to the `log.level` value
* Error Reporting - The `errorReporting` configuration key can be set to a valid bitmask to be passed into the `error_reporting()` function
* Debug - The `debug` key allows enabling the application's debug mode, this also makes the [PHP Debug Bar](http://phpdebugbar.com/) available
