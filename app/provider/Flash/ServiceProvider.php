<?php


namespace Application\Provider\Flash;

use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Flash\ServiceProvider
 *
 * @package Application\Provider\Flash
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'flash';

    protected $bannerStyle = [
        'error'   => 'alert alert-danger fade in',
        'success' => 'alert alert-success fade in',
        'notice'  => 'alert alert-info fade in',
        'warning' => 'alert alert-warning fade in',
    ];

    /**
     * {@inheritdoc}
     *
     * Register the Flash Service with the Twitter Bootstrap classes.
     *
     * @return void
     */
    public function register()
    {
        $bannerStyle = $this->bannerStyle;

        $this->di->set(
            $this->serviceName,
            function () use ($bannerStyle) {
                $flash = new Direct($bannerStyle);

                $flash->setAutoescape(true);
                $flash->setDI(container());
                $flash->setCssClasses($bannerStyle);

                return $flash;
            }
        );

        $this->di->setShared(
            'flashSession',
            function () use ($bannerStyle) {
                $flash = new Session($bannerStyle);

                $flash->setAutoescape(true);
                $flash->setDI(container());
                $flash->setCssClasses($bannerStyle);

                return $flash;
            }
        );
    }
}
