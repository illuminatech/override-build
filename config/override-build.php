<?php
/**
 * @see https://github.com/illuminatech/override-build
 * @see \Illuminatech\ArrayFactory\FactoryContract
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
        ],
    ],
];
