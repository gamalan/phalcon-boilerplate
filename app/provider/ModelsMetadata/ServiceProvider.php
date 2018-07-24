<?php


namespace Application\Provider\ModelsMetadata;

use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\ModelsMetadata\ServiceProvider
 *
 * @package Application\Provider\ModelsMetadata
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'modelsMetadata';

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
                $config = container('config')->metadata;

                $driver   = $config->drivers->{$config->default};
                $adapter  = '\Phalcon\Mvc\Model\Metadata\\' . $driver->adapter;
                $defaults = [
                    'prefix'   => $config->prefix,
                    'lifetime' => $config->lifetime,
                ];

                return new $adapter(
                    array_merge($driver->toArray(), $defaults)
                );
            }
        );
    }
}
