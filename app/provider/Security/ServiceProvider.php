<?php


namespace Application\Provider\Security;

use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Security\ServiceProvider
 *
 * @package Application\Provider\Security
 */
class ServiceProvider extends AbstractServiceProvider
{
    const DEFAULT_WORK_FACTOR = 12;

    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'security';

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
                $config = container('config');
                $security = new Security();

                $workFactor = self::DEFAULT_WORK_FACTOR;
                if (!empty($config->application->hashingFactor)) {
                    $workFactor = (int) $config->application->hashingFactor;
                }

                $security->setWorkFactor($workFactor);

                return $security;
            }
        );
    }
}
