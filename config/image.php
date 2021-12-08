<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => 'gd',

    /**
     * The root directory of all of the images.
     * if you use "make" method you can modify it by "->setRootDirectory('directory name')" method.
     */

    'root_directory' => 'images',

    /**
     * that you should pass key of your sizes array, that you defined in below section, like default one.
     * It's changeable in code too. See documentation, resize image section.
     */

    'use_size' => 'imageSizes',

    /**
     * It's for making images with these sizes.
     * Add one of these to above section("use_size").
     */

    'imageSizes' => [
        'large' => [
            'width' => '1024',
            'height' => '800'
        ],
        'small' => [
            'width' => '500',
            'height' => '400'
        ]
    ],

    /**
     * If you want to use default_size functionality, or not keep it null.
     * Write key of one of your sizes, that have been added in "use_size" section.
     * When you create image,
     * (if "default_size" not be null, and if you have more than one sizes),
     * it returns an array, which contains "default_size" key:
     *     [
     *         'index' => [
     *             'large' => '. . ./1638611107_960_large.png'
     *             'medium' => '. . ./1638611107_960_medium.png'
     *             'small' => '. . ./1638611107_960_small.png'
     *         ],
     *         'imageDirectory' => '. . .',
     *         'default_size' => 'medium'
     *      ]
     */

    'default_size' => null,
];
