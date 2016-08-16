<?php
/**
 * RegisterFacades.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/16 11:48
 */

namespace Swoole\Laravel\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;

/**
 * Class RegisterFacades
 *
 * @package Swoole\Laravel\Foundation\Bootstrap
 */
class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }
}