<?php


namespace Application\Provider\Queue;

use Application\Provider\AbstractServiceProvider;
use Application\Queue\DummyServer;

/**
 * Application\Provider\Queue\ServiceProvider
 *
 * @package Application\Provider\Queue
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'queue';

    /**
     * {@inheritdoc}
     *
     * Queue to deliver e-mails in real-time and other tasks.
     *
     * @return void
     */
    public function register()
    {
        $this->di->setShared(
            $this->serviceName,
            function () {
                $config = container('config')->queue;

                $driver  = $config->drivers->{$config->default};

                if ($config->default !== 'fake') {
                    $adapter = '\Phalcon\Queue\\' . $driver->adapter;

                    return new $adapter($driver->toArray());
                }

                return new DummyServer();
            }
        );
    }
}
