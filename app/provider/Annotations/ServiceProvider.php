<?php

namespace Application\Provider\Annotations;

use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Annotations\ServiceProvider
 *
 * @package Application\Provider\Annotations
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'annotations';

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
                $config = container('config')->annotations;

                $driver  = $config->drivers->{$config->default};
                $adapter = '\Phalcon\Annotations\Extended\Adapter\\' . $driver->adapter;

                $default = [
                    'lifetime' => $config->lifetime,
                    'prefix'   => $config->prefix,
                ];

                return new $adapter(array_merge($driver->toArray(), $default));
            }
        );
    }
}
