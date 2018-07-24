<?php

namespace Application\Provider\Database;

use Application\Listener\Database;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Database\ServiceProvider
 *
 * @package Application\Provider\Database
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'db';

    /**
     * {@inheritdoc}
     * Database connection is created based in the parameters defined in the configuration file.
     *
     * @return void
     */
    public function register()
    {
        $this->di->setShared(
            $this->serviceName,
            function () {
                $config = container('config')->database;
                $em     = container('eventsManager');

                $driver  = $config->drivers->{$config->default};
                $adapter = '\Phalcon\Db\Adapter\Pdo\\' . $driver->adapter;

                $config = $driver->toArray();
                unset($config['adapter']);

                /** @var \Phalcon\Db\Adapter\Pdo $connection */
                $connection = new $adapter($config);

                $em->attach('db', new Database());

                $connection->setEventsManager($em);

                return $connection;
            }
        );
    }
}
