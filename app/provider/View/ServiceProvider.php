<?php


namespace Application\Provider\View;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Simple;
use InvalidArgumentException;
use Phalcon\Mvc\View\Engine\Php;
use Application\Listener\ViewListener;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\View\ServiceProvider
 *
 * @package Application\Provider\View
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'view';

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
                $config = container('config')->application;
                $mode = container('bootstrap')->getMode();

                switch ($mode) {
                    case 'normal':
                        $view = new View();

                        break;
                    case 'cli':
                        $view = new Simple();

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

                $view->registerEngines([
                    '.volt' => container('volt', [$view, $this]),
                    '.php'  => Php::class,
                ]);

                $view->setViewsDir($config->viewsDir);

                $eventsManager = container('eventsManager');
                $eventsManager->attach('view', new ViewListener());

                $view->setEventsManager($eventsManager);

                return $view;
            }
        );
    }
}
