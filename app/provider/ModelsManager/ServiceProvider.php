<?php


namespace Application\Provider\ModelsManager;

use Phalcon\Mvc\Model\Manager;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\ModelsManager\ServiceProvider
 *
 * @package Application\Provider\ModelsManager
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'modelsManager';

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
                $modelsManager = new Manager();
                $modelsManager->setEventsManager(container('eventsManager'));

                return $modelsManager;
            }
        );
    }
}
