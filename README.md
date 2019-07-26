<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Laravel Materials Build Override</h1>
    <br>
</p>

This extension allows re-building materials from 3rd party libraries with patch.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/illuminatech/override-build/v/stable.png)](https://packagist.org/packages/illuminatech/override-build)
[![Total Downloads](https://poser.pugx.org/illuminatech/override-build/downloads.png)](https://packagist.org/packages/illuminatech/override-build)
[![Build Status](https://travis-ci.org/illuminatech/override-build.svg?branch=master)](https://travis-ci.org/illuminatech/override-build)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/override-build
```

or add

```json
"illuminatech/override-build": "*"
```

to the require section of your composer.json.


Usage
-----

This extension allows re-building materials from 3rd party libraries with patch.
It might be in handy in case you are using some extension, which is shipped with already compiled JavaScript files, which
you need to modify and thus re-compile. For example: extensions for 3rd party CMS like [Nova](https://nova.laravel.com/).


## Application configuration <span id="application-configuration"></span>

This extension uses [illuminatech/array-factory](https://github.com/illuminatech/array-factory) for configuration.
Make sure you are familiar with 'array factory' concept before configuring this extension.
Configuration is stored at 'config/override-build.php' file.

You can publish predefined configuration file using following console command:

```
php artisan vendor:publish --provider="Illuminatech\OverrideBuild\OverrideBuildServiceProvider" --tag=config
```
