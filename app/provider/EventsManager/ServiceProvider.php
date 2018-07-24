<?php


namespace Application\Provider\EventsManager;

use Phalcon\Events\Manager;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\EventsManager\ServiceProvider
 *
 * @package Application\Provider\EventManager
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'eventsManager';

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
                $em = new Manager();
                $em->enablePriorities(true);

                return $em;
            }
        );
    }
}
