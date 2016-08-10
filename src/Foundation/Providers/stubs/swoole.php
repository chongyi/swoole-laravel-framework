<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    | But different with default provider configuration, these providers will be
    | loaded after other providers which defined in app.php.
    |
    | You should put some providers who depends component like Request,
    | that have to be loaded before the Swoole service startup.
    */

    'providers' => [
        App\Providers\RouteServiceProvider::class,
    ],


    /*
    |--------------------------------------------------------------------------
    | Swoole Setting
    |--------------------------------------------------------------------------
    |
    | http://wiki.swoole.com/wiki/page/274.html
    |
    */

    'settings' => [
        'worker_num' => 3,
    ],
];