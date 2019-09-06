<?php
/**
 * @see https://github.com/illuminatech/override-build
 * @see \Illuminatech\ArrayFactory\FactoryContract
 * @see \Illuminatech\OverrideBuild\Builder
 */

return [
    /*
     * List of packages for override building.
     * Each package should be an 'array factory' compatible definition for {@see \Illuminatech\OverrideBuild\Builder} object.
     */
    'packages' => [
        /*
         * Example for {@see https://novapackages.com/packages/froala/nova-froala-field}
         */
        'nova-froala-field' => [
            'srcPath' => base_path('vendor/froala/nova-froala-field'),
            'srcFiles' => [
                'resources',
                '.babelrc',
                'package.json',
                'webpack.mix.js',
            ],
            'buildPath' => storage_path('build-override/nova-froala-field'),
            'overridePath' => app_path('Nova/Extensions/Froala'),
            'patches' => [
                'resources/js/field.js' => [
                    '__class' => Illuminatech\OverrideBuild\Patches\Wrap::class,
                    'template' => "{{INHERITED}}\n\nrequire('./custom-plugins');",
                ],
            ],
            'buildCommand' => [
                'yarn install',
                'yarn run '.((env('APP_ENV') === 'production') ? 'prod' : 'dev'),
            ],
        ],
        /*
         * Allow running Nova at domain sub-folder {@see https://github.com/laravel/nova-issues/issues/471}
         */
        'nova' => [
            'srcPath' => base_path('vendor/laravel/nova'),
            'buildPath' => storage_path('build-override/nova'),
            'srcFiles' => [
                'resources',
                '.babelrc',
                'mix-manifest.json',
                'package.json',
                'tailwind.js',
                'webpack.mix.js.dist',
                'yarn.lock',
            ],
            'patches' => [
                'resources/js/util/axios.js' => [
                    '__class' => Illuminatech\OverrideBuild\Patches\Replace::class,
                    'replaces' => [
                        'axios.create()' => "axios.create({baseURL: 'http://test.devel/subfolder'})"
                    ],
                ],
                'webpack.mix.js.dist' => [
                    '__class' => Illuminatech\OverrideBuild\Patches\Replace::class,
                    'replaces' => [
                        ".copy('public', '../nova-app/public/vendor/nova')" => ".copy('public', '../../public/vendor/nova')",
                    ],
                ],
                'resources/views/layout.blade.php' => [
                    '__class' => Illuminatech\OverrideBuild\Patches\Replace::class,
                    'replaces' => [
                        '"/nova-api/' => '"/subfolder/nova-api/',
                        "mix('app.css', 'vendor/nova')" => "asset(mix('app.css', 'vendor/nova'))",
                        "mix('manifest.js', 'vendor/nova')" => "asset(mix('manifest.js', 'vendor/nova'))",
                        "mix('vendor.js', 'vendor/nova')" => "asset(mix('vendor.js', 'vendor/nova'))",
                        "mix('app.js', 'vendor/nova')" => "asset(mix('app.js', 'vendor/nova'))",
                        '@json(Nova::jsonVariables(request()));' => "@json(Nova::jsonVariables(request()));\nwindow.config.base = '/subfolder' + window.config.base;"
                    ],
                ],
            ],
            'buildCommand' => [
                'yarn install',
                'yarn run '.((env('APP_ENV') === 'production') ? 'prod' : 'dev'),
            ],
        ],
    ],
];
