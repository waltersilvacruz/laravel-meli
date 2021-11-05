<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Properties for the Mercado Livre
    |--------------------------------------------------------------------------
    |
    | The configuration keys for the SDK are inconsistent in naming convention.
    |
    */

    'auth' => [
        'client_id'     => env('MELI_APP_ID'),
        'client_secret' => env('MELI_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | cURL options
    |--------------------------------------------------------------------------
    |
    | Customize curl default options
    |
    */

    'curl_default_opts' => [
        CURLOPT_USERAGENT => "MELI-API-WRAPPER-V1",
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 60
    ],

    /*
    |--------------------------------------------------------------------------
    | Properties to control logging
    |--------------------------------------------------------------------------
    |
    | Configures logging to <storage_path>/logs/quickbooks.log when in debug
    | mode or when 'QUICKBOOKS_DEBUG' is true.
    |
    */

    'logging' => [
        'enabled' => env('MELI_DEBUG', config('app.debug')),
        'location' => storage_path('logs'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Properties to configure the routes
    |--------------------------------------------------------------------------
    |
    | There are several routes that are needed for the package, so these
    | properties allow configuring them to fit the application as needed.
    |
    */

    'route' => [
        // Controls the middlewares for thr routes.  Can be a string or array of strings
        'middleware' => [
            // Added to the protected routes for the package (i.e. connect & disconnect)
            'authenticated' => false, // use 'auth' to require authentication or false to make routes public
            // Added to all routes for the package
            'default'       => 'web',
        ],
        'prefix'     => 'meli',
        'paths'      => [
            // Show forms to connect/disconnect
            'connect'    => 'connect/{state}',
            // The DELETE takes place to remove token
            'disconnect' => 'disconnect/{state}',
            // Return URI that MercadoLivre sends code to allow getting OAuth token
            'token'      => 'token',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Properties to configure the redirect route after connecting/disconnect
    |--------------------------------------------------------------------------
    |
    | You can set a default route name to be redirected after connecting or
    | disconnecting from Mercado Livre.
    |
    */

    'redirect_route' => env('MELI_REDIRECT_ROUTE'),
];
