<?php

namespace Application\Provider\Dispatcher;

use InvalidArgumentException;
use Phalcon\Mvc\Dispatcher as MvcDi;
use Phalcon\Cli\Dispatcher as CliDi;
use Application\Listener\DispatcherListener;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Dispatcher\ServiceProvider
 *
 * @package Application\Provider\Dispatcher
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'dispatcher';

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
                $mode = container('bootstrap')->getMode();

                switch ($mode) {
                    case 'normal':
                        $dispatcher = new MvcDi();
                        $dispatcher->setDefaultNamespace('Application\Controllers');

                        container('eventsManager')->attach('dispatch', new DispatcherListener(container()));

                        break;
                    case 'cli':
                        $dispatcher = new CliDi();
                        $dispatcher->setDefaultNamespace('Application\Task');

                        $dispatcher->setActionSuffix('');
                        $dispatcher->setTaskSuffix('');
                        $dispatcher->setDefaultTask('help');

                        break;
                    case 'api':
                        throw new InvalidArgumentException(
                            'Not implemented yet.'
                        );
                    default:
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid application mode. Expected either "normal" or "cli" or "api". Got "%s".',
                                is_scalar($mode) ? $mode : var_export($mode, true)
                            )
                        );
                }

                $dispatcher->setDI(container());
                $dispatcher->setEventsManager(container('eventsManager'));

                return $dispatcher;
            }
        );
    }
}
