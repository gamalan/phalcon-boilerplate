<?php


namespace Application\Provider\Mailer;

use Phalcon\Mailer\Manager;
use InvalidArgumentException;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Mail\ServiceProvider
 *
 * @package Application\Provider\Mailer
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'mailer';

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
                /** @var \Phalcon\Config $config */
                $config = container('config')->mailer;
                $driver = $config->get('driver');

                switch ($driver) {
                    case 'smtp':
                    case 'mail':
                    case 'sendmail':
                        $mailerConfig = $config->toArray();

                        $manager = new Manager($mailerConfig);
                        $manager->setDI(container());

                        return $manager;
                }

                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid mail driver. Expected either "smtp" or "mail" or "sendmail". Got "%s".',
                        is_scalar($driver) ? $driver : var_export($driver, true)
                    )
                );
            }
        );
    }
}
