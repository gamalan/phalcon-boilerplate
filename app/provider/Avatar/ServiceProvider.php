<?php


namespace Application\Provider\Avatar;

use Phalcon\Avatar\Gravatar;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Avatar\ServiceProvider
 *
 * @package Application\Provider\Avatar
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'gravatar';

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
                return new Gravatar(container('config')->get('gravatar'));
            }
        );
    }
}
