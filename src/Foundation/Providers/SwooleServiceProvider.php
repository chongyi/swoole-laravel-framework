<?php
/**
 * SwooleServiceProvider.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/11 01:35
 */

namespace Swoole\Laravel\Foundation\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class SwooleServiceProvider
 *
 * @package Swoole\Laravel\Foundation\Providers
 */
class SwooleServiceProvider extends ServiceProvider
{
    /**
     * Boot the provider
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/stubs/swoole.php'     => config_path('swoole.php'),
            __DIR__ . '/stubs/swoole-app.php' => base_path('bootstrap/swoole-app.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}