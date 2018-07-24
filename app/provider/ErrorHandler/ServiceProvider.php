<?php


namespace Application\Provider\ErrorHandler;

use Whoops\Run;
use InvalidArgumentException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use Application\Exception\Handler\LoggerHandler;
use Application\Provider\AbstractServiceProvider;
use Application\Exception\Handler\ErrorPageHandler;

/**
 * Application\Provider\ErrorHandler\ServiceProvider
 *
 * @package Application\Provider\Environment
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName = 'errorHandler';

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register()
    {
        $this->di->setShared("{$this->serviceName}.loggerHandler", LoggerHandler::class);
        $this->di->setShared("{$this->serviceName}.prettyPageHandler", PrettyPageHandler::class);
        $this->di->setShared("{$this->serviceName}.errorPageHandler", ErrorPageHandler::class);

        $this->di->setShared(
            "{$this->serviceName}.jsonResponseHandler",
            function () {
                $handler = new JsonResponseHandler();
                $handler->setJsonApi(true);

                return $handler;
            }
        );

        $service = $this->serviceName;

        $this->di->setShared(
            $this->serviceName,
            function () use ($service) {
                $run  = new Run();
                $mode = container('bootstrap')->getMode();

                switch ($mode) {
                    case 'normal':
                        if (env('APP_DEBUG', false)) {
                            $run->pushHandler(container("{$service}.prettyPageHandler"));
                        } else {
                            $run->pushHandler(container("{$service}.errorPageHandler"));
                        }
                        break;
                    case 'cli':
                        // @todo
                        break;
                    case 'api':
                        $run->pushHandler(container("{$service}.jsonResponseHandler"));
                        throw new InvalidArgumentException(
                            'Not implemented yet.'
                        );
                        break;
                    default:
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid application mode. Expected either "normal" or "cli" or "api". Got "%s".',
                                is_scalar($mode) ? $mode : var_export($mode, true)
                            )
                        );
                }

                $run->pushHandler(container("{$service}.loggerHandler"));

                return $run;
            }
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function boot()
    {
        container($this->serviceName)->register();
    }
}
