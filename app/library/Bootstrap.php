<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/5/18
 * Time: 7:23 PM
 */

namespace Application;

use Phalcon\Di;
use Phalcon\DiInterface;
use Application\Provider;
use InvalidArgumentException;
use Phalcon\Di\FactoryDefault;
use Application\Console\Application as Console;
use Phalcon\Mvc\Application as MvcApplication;
use Application\Provider\ServiceProviderInterface;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Route;

class Bootstrap {
	/**
	 * The internal application core.
	 * @var \Phalcon\Application
	 */
	private $app;

	/**
	 * The application mode.
	 * @var string
	 */
	private $mode;

	/**
	 * The Dependency Injection Container
	 * @var DiInterface
	 */
	private $di;

	/**
	 * Current application environment:
	 * production, staging, development, testing
	 * @var string
	 */
	private $environment;

	/**
	 * Active router
	 * @var string
	 */
	private $route;

	/**
	 * Bootstrap constructor.
	 *
	 * @param string $mode The application mode: "normal", "cli", "api".
	 */
	public function __construct( $mode = 'normal', $route = 'app' ) {
		$this->mode  = $mode;
		$this->route = $route;

		$this->di = new FactoryDefault();

		container()->setShared( 'bootstrap', $this );

		Di::setDefault( $this->di );

		/**
		 * These services should be registered first
		 */
		$this->initializeServiceProvider( new Provider\EventsManager\ServiceProvider( $this->di ) );
		$this->setupEnvironment();
		$this->initializeServiceProvider( new Provider\ErrorHandler\ServiceProvider( $this->di ) );

		$this->createInternalApplication();

		/** @noinspection PhpIncludeInspection */
		$providers = require config_path( 'providers.php' );
		if ( is_array( $providers ) ) {
			$this->initializeServiceProviders( $providers );
		}

		$this->app->setEventsManager( container( 'eventsManager' ) );

		container()->setShared( 'app', $this->app );
		$this->app->setDI( $this->di );

		/** @noinspection PhpIncludeInspection */
		$services = require config_path( 'services.php' );
		if ( is_array( $services ) ) {
			$this->initializeServices( $services );
		}
	}

	/**
	 * Runs the Application
	 *
	 * @return mixed
	 */
	public function run() {
		return $this->getOutput();
	}

	/**
	 * Get the Application.
	 *
	 * @return \Phalcon\Application|\Phalcon\Mvc\Micro
	 */
	public function getApplication() {
		return $this->app;
	}

	/**
	 * Get application output.
	 *
	 * @return string
	 */
	public function getOutput() {
		//try {
		if ( $this->app instanceof MvcApplication ) {
			return $this->app->handle()->getContent();
		}

		return $this->app->handle();
		/*} catch ( \Throwable $e ) {

			echo $e->getTraceAsString();

		}*/
	}

	/**
	 * Gets current application environment: production, staging, development, testing, etc.
	 *
	 * @return string
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * Gets current application mode: normal, cli, api.
	 *
	 * @return string
	 */
	public function getMode() {
		return $this->mode;
	}

	/**
	 * Gets current active route
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * Get app mode+route
	 * @return string
	 */
	public function getAppMode() {
		return $this->getMode() . "." . $this->getRoute();
	}

	/**
	 * Initialize the Service Providers.
	 *
	 * @param  string[] $providers
	 *
	 * @return $this
	 */
	protected function initializeServiceProviders( array $providers ) {
		foreach ( $providers as $name => $class ) {
			$this->initializeServiceProvider( new $class( $this->di ) );
		}

		return $this;
	}

	/**
	 * Initialize the Service Provider.
	 *
	 * Usually the Service Provider register a service in the Dependency Injector Container.
	 *
	 * @param  ServiceProviderInterface $serviceProvider
	 *
	 * @return $this
	 */
	protected function initializeServiceProvider( ServiceProviderInterface $serviceProvider ) {
		$serviceProvider->register();
		$serviceProvider->boot();

		return $this;
	}

	/**
	 * Create internal application to handle requests.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function createInternalApplication() {
		switch ( $this->mode ) {
			case 'normal':
				$this->app = new MvcApplication( $this->di );
				break;
			case 'cli':
				$this->app = new Console( $this->di );
				break;
			case 'api':
				throw new InvalidArgumentException(
					'Not implemented yet.'
				);
			default:
				throw new InvalidArgumentException(
					sprintf(
						'Invalid application mode. Expected either "normal" or "cli" or "api". Got "%s".',
						is_scalar( $this->mode ) ? $this->mode : var_export( $this->mode, true )
					)
				);
		}
	}

	/**
	 * Setting up the application environment.
	 *
	 * This tries to get `APP_ENV` environment variable from $_ENV.
	 * If failed the `development` will be used.
	 *
	 * After getting `APP_ENV` variable we set the Bootstrap::$environment
	 * and the `APPLICATION_ENV` constant.
	 */
	protected function setupEnvironment() {
		$this->environment = env( 'APP_ENV', ENV_DEVELOPMENT );

		defined( 'APPLICATION_ENV' ) || define( 'APPLICATION_ENV', $this->environment );

		$this->initializeServiceProvider( new Provider\Environment\ServiceProvider( $this->di ) );
	}

	/**
	 * Register services in the Dependency Injector Container.
	 * This allows to inject dependencies by using abstract classes.
	 *
	 * <code>
	 * $services = [
	 *     '\My\Namespace\Services\UserInterface' => '\My\Concrete\UserService',
	 * ];
	 *
	 * $bootstrap->initializeModelServices($services)
	 * </code>
	 *
	 * @param  string[] $services
	 *
	 * @return $this
	 */
	protected function initializeServices( array $services ) {
		foreach ( $services as $abstract => $concrete ) {
			container()->setShared( $abstract, $concrete );
		}

		return $this;
	}
}