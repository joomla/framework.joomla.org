# Joomla! Framework Site

This application is the code powering the [framework.joomla.org](https://framework.joomla.org) website.

## Requirements

* PHP 7.0+
* PDO with MySQL support
* Composer
* Apache with mod_rewrite enabled and configured to allow the .htaccess file to be read
* NPM to compile assets

## Installation

1. Clone this repo on your web server
2. Run the `composer install` command to install all dependencies
3. Copy `etc/config.dist.json` to `etc/config.json` and configure your environment
4. Run `vendor/bin/phinx migrate` to set up the database
5. Run `npm install` to install dependencies
6. Run `npm run prod` to compile assets (SCSS, JS) for production
