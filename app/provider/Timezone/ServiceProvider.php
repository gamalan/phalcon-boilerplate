<?php


namespace Application\Provider\Timezone;

use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Timezone\ServiceProvider
 *
 * @package Application\Provider\Timezones
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'timezones';

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
                /** @noinspection PhpIncludeInspection */
                return require config_path('timezones.php');
            }
        );
    }
}
