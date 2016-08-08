<?php
/**
 * Kernel.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/07 23:19
 */

namespace Swoole\Laravel\Foundation\Http;

use Illuminate\Foundation\Http\Kernel as LaravelKernel;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Facade;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server;
use Swoole\Laravel\Foundation\Bootstrap\RegisterProviders;
use Swoole\Laravel\Http\Request;

/**
 * Class Kernel
 *
 * @package Swoole\Laravel\Foundation\Http
 */
class Kernel extends LaravelKernel
{
    /**
     * @var Server
     */
    protected $swoole;

    /**
     * Set the swoole server instance
     *
     * @param $server
     */
    public function setSwooleServer($server)
    {
        $this->swoole = $server;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->swooleBootstrap();

        return (new Pipeline($this->app))
            ->send($request)
            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
            ->then($this->dispatchToRouter());
    }

    /**
     * Bootstrap for swoole service
     */
    protected function swooleBootstrap()
    {
        $this->app->bootstrapWith([
            RegisterProviders::class,
        ]);
    }


    /**
     * Start the swoole http service
     */
    public function start()
    {
        // This's default bootstrap process, but it's moved here because most of boot options should not be reloaded.
        $this->bootstrap();

        $this->swoole->on('request', function (SwooleRequest $request, SwooleResponse $response) {
            $realRequest  = Request::captureViaSwooleRequest($request);
            $realResponse = $this->handle($realRequest);

            foreach ($realResponse->headers->allPreserveCase() as $name => $values) {
                foreach ($values as $value) {
                    $response->header($name, $value);
                }
            }

            $response->status($realResponse->getStatusCode());
            $response->end($realResponse->getContent());
        });

        $this->swoole->start();
    }
}