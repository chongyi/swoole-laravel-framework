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
            ->load($this->config['app.swoole_providers']);
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

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        $aliases = [
            'app'                  => ['Illuminate\Foundation\Application', 'Illuminate\Contracts\Container\Container', 'Illuminate\Contracts\Foundation\Application'],
            'auth'                 => 'Illuminate\Auth\AuthManager',
            'auth.driver'          => ['Illuminate\Auth\Guard', 'Illuminate\Contracts\Auth\Guard'],
            'auth.password.tokens' => 'Illuminate\Auth\Passwords\TokenRepositoryInterface',
            'blade.compiler'       => 'Illuminate\View\Compilers\BladeCompiler',
            'cache'                => ['Illuminate\Cache\CacheManager', 'Illuminate\Contracts\Cache\Factory'],
            'cache.store'          => ['Illuminate\Cache\Repository', 'Illuminate\Contracts\Cache\Repository'],
            'config'               => ['Illuminate\Config\Repository', 'Illuminate\Contracts\Config\Repository'],
            'cookie'               => ['Illuminate\Cookie\CookieJar', 'Illuminate\Contracts\Cookie\Factory', 'Illuminate\Contracts\Cookie\QueueingFactory'],
            'encrypter'            => ['Illuminate\Encryption\Encrypter', 'Illuminate\Contracts\Encryption\Encrypter'],
            'db'                   => 'Illuminate\Database\DatabaseManager',
            'db.connection'        => ['Illuminate\Database\Connection', 'Illuminate\Database\ConnectionInterface'],
            'events'               => ['Illuminate\Events\Dispatcher', 'Illuminate\Contracts\Events\Dispatcher'],
            'files'                => 'Illuminate\Filesystem\Filesystem',
            'filesystem'           => ['Illuminate\Filesystem\FilesystemManager', 'Illuminate\Contracts\Filesystem\Factory'],
            'filesystem.disk'      => 'Illuminate\Contracts\Filesystem\Filesystem',
            'filesystem.cloud'     => 'Illuminate\Contracts\Filesystem\Cloud',
            'hash'                 => 'Illuminate\Contracts\Hashing\Hasher',
            'translator'           => ['Illuminate\Translation\Translator', 'Symfony\Component\Translation\TranslatorInterface'],
            'log'                  => ['Illuminate\Log\Writer', 'Illuminate\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
            'mailer'               => ['Illuminate\Mail\Mailer', 'Illuminate\Contracts\Mail\Mailer', 'Illuminate\Contracts\Mail\MailQueue'],
            'auth.password'        => ['Illuminate\Auth\Passwords\PasswordBroker', 'Illuminate\Contracts\Auth\PasswordBroker'],
            'queue'                => ['Illuminate\Queue\QueueManager', 'Illuminate\Contracts\Queue\Factory', 'Illuminate\Contracts\Queue\Monitor'],
            'queue.connection'     => 'Illuminate\Contracts\Queue\Queue',
            'redirect'             => 'Illuminate\Routing\Redirector',
            'redis'                => ['Illuminate\Redis\Database', 'Illuminate\Contracts\Redis\Database'],
            'request'              => ['Illuminate\Http\Request', 'Swoole\Laravel\Http\Request'],
            'router'               => ['Illuminate\Routing\Router', 'Illuminate\Contracts\Routing\Registrar'],
            'session'              => 'Illuminate\Session\SessionManager',
            'session.store'        => ['Illuminate\Session\Store', 'Symfony\Component\HttpFoundation\Session\SessionInterface'],
            'url'                  => ['Illuminate\Routing\UrlGenerator', 'Illuminate\Contracts\Routing\UrlGenerator'],
            'validator'            => ['Illuminate\Validation\Factory', 'Illuminate\Contracts\Validation\Factory'],
            'view'                 => ['Illuminate\View\Factory', 'Illuminate\Contracts\View\Factory'],
        ];

        foreach ($aliases as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }


}