<?php


namespace Application\Provider\Session;

use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Session\ServiceProvider
 *
 * @package Application\Provider\Session
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'session';

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
                $config = container('config')->session;

                $driver   = $config->drivers->{$config->default};
                $adapter  = '\Phalcon\Session\Adapter\\' . $driver->adapter;
                $defaults = [
                    'prefix'   => $config->prefix,
                    'uniqueId' => $config->uniqueId,
                    'lifetime' => $config->lifetime,
                ];

                /** @var \Phalcon\Session\AdapterInterface $session */
                $session = new $adapter(array_merge($driver->toArray(), $defaults));
                $session->start();

                return $session;
            }
        );
    }
}
