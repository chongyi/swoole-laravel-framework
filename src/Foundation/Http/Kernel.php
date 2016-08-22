<?php
/**
 * Kernel.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/07 23:19
 */

namespace Swoole\Laravel\Foundation\Http;

use Illuminate\Container\Container;
use Illuminate\Foundation\Http\Kernel as LaravelKernel;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Laravel\Foundation\Bootstrap\RegisterFacades;
use Swoole\Laravel\Foundation\Bootstrap\RegisterProviders;
use Swoole\Laravel\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * @var
     */
    protected $backupApplication;

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
        $this->rebuildApplication();

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
            RegisterFacades::class,
            RegisterProviders::class,
        ]);
    }

    /**
     * Get swoole server instance
     *
     * @return Server
     */
    public function getSwooleServer()
    {
        return $this->swoole;
    }

    /**
     * Start the swoole http service
     */
    public function start()
    {
        // This's default bootstrap process, but it's moved here because most of boot options should not be reloaded.
        $this->bootstrap();

        // Read swoole configure file.
        $settings = $this->getApplication()->make('config')->get('swoole.settings');

        $this->swoole->set($settings);
        $this->swoole->on('request', [$this, 'onRequest']);

        $this->swoole->start();
    }

    /**
     * Terminate the http request process
     *
     * @param Response                                                      $swooleResponse
     * @param \Symfony\Component\HttpFoundation\Response|BinaryFileResponse $realResponse
     */
    protected function terminateRequestProcess(Response $swooleResponse, $realResponse)
    {
        if ($realResponse instanceof BinaryFileResponse) {
            $swooleResponse->sendfile($realResponse->getFile()->getPathname());
        } else {
            $swooleResponse->end($realResponse->getContent());
        }
    }

    /**
     * Rebuild application when swoole server get a new request.
     */
    protected function rebuildApplication()
    {
        if ($this->backupApplication) {
            $this->app = clone $this->backupApplication;
        } else {
            $this->backupApplication = clone $this->app;
        }

        // Reset container instance
        Container::setInstance($this->app);

        $this->app->rebuild();
        $this->router = $this->app->make(Router::class);

        foreach ($this->middlewareGroups as $key => $middleware) {
            $this->router->middlewareGroup($key, $middleware);
        }

        foreach ($this->routeMiddleware as $key => $middleware) {
            $this->router->middleware($key, $middleware);
        }
    }

    /**
     * To service new request.
     *
     * @param SwooleRequest $request
     * @param Response      $response
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        $realRequest  = Request::captureViaSwooleRequest($request);
        $realResponse = $this->handle($realRequest);

        $this->formatResponse($response, $realResponse);

        // Terminate the process
        $this->terminate($realRequest, $realResponse);
    }

    /**
     * @param Response $response
     * @param          $realResponse
     */
    protected function formatResponse(SwooleResponse $response, $realResponse)
    {
        // Build header.
        foreach ($realResponse->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        // Build cookies.
        foreach ($realResponse->headers->getCookies() as $cookie) {
            $response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        // Set HTTP status code into the swoole response.
        $response->status($realResponse->getStatusCode());
        $this->terminateRequestProcess($response, $realResponse);
    }
}