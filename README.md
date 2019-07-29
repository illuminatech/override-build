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

We can take [froala/nova-froala-field](https://github.com/froala/nova-froala-field) for example. This package is shipped with
JavaScript WYSIWYG editor integrated into VueJS component, which are compiled altogether into a single '*.js' file.
In case you need to apply [custom plugin](https://www.froala.com/wysiwyg-editor/docs/concepts/custom/button) to the editor, it becomes
impossible unless you re-compile the extension with your own changes.

At this stage it might be tempting to simply adjust source files inside "vendor/froala/nova-froala-field" directory and run
NPM build from there. However any manual changes inside the vendor directory will cause you problems in the future.
The patch you create in this way can not be tracked by VCS and you will have to re-apply it in case you update the library.
Also any changes made to the files in "vendor" directory may cause Composer fail on "install" or "update" command.

This package was created to solve the problem. It allows creating new build (compilation) from particular source files under
the different path. This task is performed in following steps:

 - Copy source files, which are out of VCS control, to the new 'build' directory.
 
 - Append/override copied files using ones from 'override' directory, which may be under VCS control.
 
 - Apply pre-defined patches, like search-replace or JSON modification, to the created files.
 
 - Run the build/compilation over composed 'build' directory files.

**Heads up!** Actually re-building 3rd party materials in this way is a hack. You should not use it unless you can not
achieve your goal using other means.


## Application configuration <span id="application-configuration"></span>

This extension uses [illuminatech/array-factory](https://github.com/illuminatech/array-factory) for configuration.
Make sure you are familiar with 'array factory' concept before configuring this extension.
Configuration is stored at 'config/override-build.php' file.

You can publish predefined configuration file using following console command:

```
php artisan vendor:publish --provider="Illuminatech\OverrideBuild\OverrideBuildServiceProvider" --tag=config
```

Inside the configuration file you'll have to define the list of packages you wish to re-build. For our 'Froala' example the
configuration may look like following:

```php
<?php

return [
    'packages' => [
        'nova-froala-field' => [
            'srcPath' => base_path('vendor/froala/nova-froala-field'), // directory to get source files from
            'srcFiles' => [ // probably you do not need to copy every vendor file, in this case you can list the needed ones here
                'resources',
                '.babelrc',
                'package.json',
                'webpack.mix.js',
            ],
            'buildPath' => storage_path('build-override/nova-froala-field'), // in this directory the new build will take place.
            'overridePath' => app_path('Nova/Extensions/Froala'), // any file from this directory will be append to the source ones before build
            'patches' => [ // list of patches to be applied to the source files.
                'resources/js/field.js' => [
                    '__class' => Illuminatech\OverrideBuild\Patches\Wrap::class,
                    'template' => "{{INHERITED}}\n\nrequire('./custom-plugins');",
                ],
            ],
            'buildCommand' => [ // shell commands to be executed for the build creation
                'yarn install',
                'yarn run prod',
            ],
        ],
    ],
];
```

Each package specification is an 'array factory' compatible configuration for the `\Illuminatech\OverrideBuild\Builder` instance.
Please refer to `\Illuminatech\OverrideBuild\Builder` class for more details about particular options.

Once configuration is complete you are able to run re-build using 'override-build' artisan command. This command accepts the
package name from the configuration as an argument, specifying which package should be re-built. For example:

```
php artisan override-build nova-froala-field
```


## Overriding files <span id="overriding-files"></span>

You would not need to re-build some package without making any modifications to its source. The easiest way to do so is using
the 'override' directory. It should repeat the structure of the source directory containing only those files, which should be 
appended or replaced. In our example for the 'Froala Nova field' the source directory has the following structure:

```
config/
database/
dist/
resources/
    components/
        DetailField.vue
        FormField.vue
        IndexField.vue
    js/
        FroalaAttachmentsAdapter.js
        MediaConfigurator.js
        PluginsLoader.js
        TrixAttachmentsAdapter.js
        field.js
routes/
src/
.babelrc
package.json
```

The override directory structure for it may look like following:

```
resources/
    js/
        field.js
        custom-plugins.js
```

Its application will replace 'resources/js/field.js' file and append 'resources/js/custom-plugins.js'.

While original 'resources/js/field.js' file content looks like following:

```typescript
require('froala-editor/js/froala_editor.pkgd.min');
require('froala-editor/js/plugins.pkgd.min.js');

import VueFroala from 'vue-froala-wysiwyg';

Nova.booting(Vue => {
    Vue.use(VueFroala);

    Vue.component('index-nova-froala-field', require('./components/IndexField'));
    Vue.component('detail-nova-froala-field', require('./components/DetailField'));
    Vue.component('form-nova-froala-field', require('./components/FormField'));
});
```

The override may contain extra code adding the custom editor plugins defined in 'resources/js/custom-plugins.js' file:

```typescript
require('froala-editor/js/froala_editor.pkgd.min');
require('froala-editor/js/plugins.pkgd.min.js');

require('./custom-plugins'); // add custom plugins to the build

import VueFroala from 'vue-froala-wysiwyg';

Nova.booting(Vue => {
    Vue.use(VueFroala);

    Vue.component('index-nova-froala-field', require('./components/IndexField'));
    Vue.component('detail-nova-froala-field', require('./components/DetailField'));
    Vue.component('form-nova-froala-field', require('./components/FormField'));
});
```


## Patching files <span id="patching-files"></span>

While complete overriding of the source file is the most simple way to apply your modifications, it has some significant drawbacks.
You will need to copy all original file content into the override and then make your modification, even if changes a single line of code.
In case source library upgrades it may change the file you have overridden in the way build ends with an error with your version.
In order to make your changes more persistent `\Illuminatech\OverrideBuild\Builder::$patches` has been created.
Each patch is a PHP object matching `\Illuminatech\OverrideBuild\PatchContract`, which modifies file content.
Following pre-defined patches are available:

 - `\Illuminatech\OverrideBuild\Patches\Replace` - replaces set of strings.
 
 - `\Illuminatech\OverrideBuild\Patches\Wrap` - wraps an original content into specified string, allowing append/prepend the extra lines.
 
 - `\Illuminatech\OverrideBuild\Patches\Json` - allows modification into the JSON structure.
 
Please refer to the particular patch class for more details.

For our 'Froala' example we can simply patch 'resources/js/field.js' adding an extra line with `require('./custom-plugins');` instead
of rewriting it as whole.

```php
<?php

return [
    'packages' => [
        'nova-froala-field' => [
            // ...
            'patches' => [
                'resources/js/field.js' => [
                    // wrap the original file content, appending `require('./custom-plugins');` to the end of the file
                    '__class' => Illuminatech\OverrideBuild\Patches\Wrap::class,
                    'template' => "{{INHERITED}}\n\nrequire('./custom-plugins');",
                ],
            ],
            // ...
        ],
    ],
];
```
