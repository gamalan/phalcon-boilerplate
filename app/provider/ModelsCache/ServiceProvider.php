<?php


namespace Application\Provider\ModelsCache;

use Phalcon\Cache\Frontend\Data;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\ModelsCache\ServiceProvider
 *
 * @package Application\Provider\ModelsCache
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'modelsCache';

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register()
    {
        $this->di->setShared(
            $this->serviceName,
            function () {
                $config = container('config')->cache;

                $driver  = $config->drivers->{$config->default};
                $adapter = '\Phalcon\Cache\Backend\\' . $driver->adapter;
                $default = [
                    'statsKey' => 'SMC:'.substr(md5($config->prefix), 0, 16).'_',
                    'prefix'   => 'PMC_'.$config->prefix,
                ];

                return new $adapter(
                    new Data(['lifetime' => $config->lifetime]),
                    array_merge($driver->toArray(), $default)
                );
            }
        );
    }
}
