<?php


namespace Application\Provider\Assets;

use Phalcon\Assets\Manager;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Assets\ServiceProvider
 *
 * @package Application\Provider\Assets
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'assets';

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register()
    {
        $this->di->setShared($this->serviceName, Manager::class);
    }
}
