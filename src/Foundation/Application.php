<?php
/**
 * Application.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/06 22:40
 */

namespace Swoole\Laravel\Foundation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Foundation\ProviderRepository;

/**
 * Class Application
 *
 * @package Swoole\Laravel\Foundation
 */
class Application extends LaravelApplication
{
    /**
     * @var bool
     */
    protected $baseBoot = false;

    /**
     * @var array
     */
    protected $bootedServiceProviders = [];

    /**
     * Register all of the configured providers (without swoole service providers).
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $manifestPath = $this->getCachedServicesPath();

        // For compatibility
        (new ProviderRepository($this, new Filesystem, $manifestPath))
            ->load(array_diff($this->config['app.providers'], $this->config['app.swoole_providers']));
    }


    /**
     * Register all of the configured swoole providers
     */
    public function registerSwooleConfiguredProviders()
    {
        $manifestPath = $this->basePath() . '/bootstrap/cache/swoole-services.json';

        (new ProviderRepository($this, new Filesystem(), $manifestPath))
            ->load($this->config['app.swoole-providers']);
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($p) {
            $class = get_class($p);
            if (!$this->baseBoot || !in_array($class, $this->bootedServiceProviders)) {
                $this->bootProvider($p);

                // Now record the registered providers, when swoole server started
                // and boot again, that will be skipping recorded providers.
                $this->bootedServiceProviders[] = $class;
            }
        });

        // Replace default property, because it's has not been booted really.
        $this->baseBoot = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return parent::isBooted() && $this->baseBoot;
    }

    /**
     * Swoole boot process
     */
    public function swooleBoot()
    {
        $this->boot();

        // Finished boot process
        $this->booted = true;
    }
}