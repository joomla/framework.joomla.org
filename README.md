# Joomla! Framework Site

This application is the code powering the [Joomla! Framework](https://framework.joomla.org) website.

## Requirements

* PHP 7.2+
    * PDO with MySQL support
* Composer
* Apache with mod_rewrite enabled
* If changing web assets, NPM is required

## Installation

1. Clone this repo on your web server
2. Run the `composer install` command to install all dependencies
3. Copy `etc/config.dist.json` to `etc/config.json` and configure your environment (see below for full details on the configuration)
4. Run `vendor/bin/phinx migrate` to set up the database

### Seeding Database

To seed the database with the Framework package data, the following steps should be taken:

1. Run the `bin/framework package:sync` command, this will load the packages table based on the contents of the `packages.yml` file at the repository root
2. Run the `bin/framework packagist:sync:releases` command, this will load the releases table with all stable releases that have been tagged based on the Packagist API response
3. Run the `bin/framework packagist:sync:downloads` command, this will load the download counts for all packages into the database
4. (NOT REQUIRED SINCE PAGE IS DISABLED) Run the `bin/framework github:contributors` command, this will query the GitHub API to get all code contributors for each package 

## Modifying Web Assets

The web assets (CSS, JavaScript, and images) are compiled and processed with [Laravel Mix](https://github.com/JeffreyWay/laravel-mix) which is a wrapper around webpack. The primary source for all assets is the `assets` directory, running Mix will place production assets into the `www/media` directory.

The use of Mix requires [NPM](https://www.npmjs.com/) as hinted at in the Installation section.  NPM 5+ and Node 8+ are required.  Three scripts are available:

* `npm run prod` will compile assets for production, which includes compression and minification; code must be committed to this repository with the production configuration
* `npm run dev` will compile the assets without production optimizations
* `npm run watch` is the same as the `dev` script but with the `--watch` flag passed

This site makes use of [Subresource Integrity (SRI)](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity), therefore the integrity hashes must be regenerated after compiling assets. This can be done by running the `bin/framework template:generate-sri` command.

## Database Schema

The database schema is managed through [Phinx](https://phinx.org/).  The `phinx.php` file at the root of this repo configures the Phinx environment.  Please see their documentation for more information.

## Application Configuration

The application's configuration is defined as follows:

* Database - The `joomla/database` package is used to provide a database connection as required
    * `database.host` - The address of the database server
    * `database.user` - The user to connect to the database as
    * `database.password` - The password for the database user
    * `database.database` - The name of the database to use
    * `database.prefix` - The prefix to use for the database's tables
* Template - The `twig/twig` package is used for the application's templates
    * `template.debug` - Flag to enable Twig's debug functionality, when enabled the caching functionality is not available
    * `template.cache.enabled` - Flag to enable Twig's caching functionality
    * `template.cache.path` - The path relative to the repo root where cached Twig files should be stored
* Logging - The `monolog/monolog` package is used for logging functionality
    * `log.level` - The default logging level to use for all application loggers, this defaults to the `ERROR` level
    * `log.application` - The logging level to use specifically for the `monolog.handler.application` logger; defaults to the `log.level` value
* GitHub - The `joomla/github` package is used to interface with the GitHub API for some of the site's capabilities
    * `gh.token` - Set a GitHub API token to use for authenticating to the API, this is the recommended setting
    * `api.username` - Set the GitHub account username to use for authenticating to the API
    * `api.password` - Set the GitHub account password to use for authenticating to the API
* Error Reporting - The `errorReporting` configuration key can be set to a valid bitmask to be passed into the `error_reporting()` function
* Debug - The `debug` key allows enabling the application's debug mode, this also makes the [PHP Debug Bar](http://phpdebugbar.com/) available
