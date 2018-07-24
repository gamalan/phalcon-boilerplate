<?php


namespace Application\Provider\ViewCache;

use Phalcon\Cache\Frontend\Output;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\ViewCache\ServiceProvider
 *
 * @package Application\Provider\ViewCache
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'viewCache';

    /**
     * {@inheritdoc}
     *
     * Note: The frontend must always be Phalcon\Cache\Frontend\Output and the
     * service 'viewCache' must be registered as always open (not shared) in
     * the services container (DI).
     *
     * @return void
     */
    public function register()
    {
        $this->di->set(
            $this->serviceName,
            function () {
                $config = container('config')->cache;

                $driver  = $config->drivers->{$config->views};
                $adapter = '\Phalcon\Cache\Backend\\' . $driver->adapter;
                $default = [
                    'statsKey' => 'SVC:'.substr(md5($config->prefix), 0, 16).'_',
                    'prefix'   => 'PVC_'.$config->prefix,
                ];

                return new $adapter(
                    new Output(['lifetime' => $config->lifetime]),
                    array_merge($driver->toArray(), $default)
                );
            }
        );
    }
}
