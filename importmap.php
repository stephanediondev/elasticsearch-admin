<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'app-database' => [
        'path' => './assets/app-database.js',
        'entrypoint' => true,
    ],
    'app-enrich' => [
        'path' => './assets/app-enrich.js',
        'entrypoint' => true,
    ],
    'app-subscription' => [
        'path' => './assets/app-subscription.js',
        'entrypoint' => true,
    ],
    'bootstrap' => [
        'version' => '5.3.7',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.7',
        'type' => 'css',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'slug' => [
        'version' => '11.0.0',
    ],
    'file-saver' => [
        'version' => '2.0.5',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.13.1',
        'type' => 'css',
    ],
];
