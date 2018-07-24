<?php


namespace Application\Provider\VoltTemplate;

use Application\Html\VoltFunctions;
use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\ViewBaseInterface;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\VoltTemplate\ServiceProvider
 *
 * @package Application\Provider\VoltTemplate
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The Service name.
	 * @var string
	 */
	protected $serviceName = 'volt';

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function register() {
		$service = function ( ViewBaseInterface $view, DiInterface $di = null ) {
			$volt = new Volt( $view, $di ?: container() );

			$volt->setOptions(
				[
					'compiledPath'  => function ( $path ) {
						$path     = trim( substr( $path, strlen( dirname( app_path() ) ) ), '\\/' );
						$filename = basename( str_replace( [ '\\', '/' ], '_', $path ), '.volt' ) . '.php';
						$cacheDir = cache_path( 'volt' );

						if ( ! is_dir( $cacheDir ) ) {
							@mkdir( $cacheDir, 0755, true );
						}

						return $cacheDir . DIRECTORY_SEPARATOR . $filename;
					},
					'compileAlways' => environment( 'development' ) || env( 'APP_DEBUG', false ),
				]
			);

			$volt->getCompiler()->addExtension( new VoltFunctions() );

			return $volt;
		};

		$this->di->setShared( $this->serviceName, $service );
	}
}
