<?php


namespace Application\Provider\Config;

use RuntimeException;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\Config\ServiceProvider
 *
 * @package Application\Provider\Config
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The Service name.
	 * @var string
	 */
	protected $serviceName = 'config';

	/**
	 * Config files.
	 * @var array
	 */
	protected $configs = [
		'logger',
		'cache',
		'session',
		'database',
		'metadata',
		'queue',
		'devtools',
		'annotations',
		'mail',
		'config',
	];

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function boot() {
		$configPath = config_path( 'config.php' );

		if ( ! file_exists( $configPath ) || ! is_file( $configPath ) ) {
			throw new RuntimeException(
				sprintf(
					'The application config not found. Please make sure that the file "%s" is present',
					$configPath
				)
			);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function register() {
		$configs = $this->configs;

		$this->di->setShared(
			$this->serviceName,
			function () use ( $configs ) {
				return Factory::create( $configs );
			}
		);
	}
}
