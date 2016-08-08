<?php
/**
 * RegisterProviders.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/08 00:18
 */

namespace Swoole\Laravel\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders as LaravelBootstrap;

class RegisterProviders extends LaravelBootstrap
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application|\Swoole\Laravel\Foundation\Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->registerSwooleConfiguredProviders();
        $app->swooleBoot();
    }

}