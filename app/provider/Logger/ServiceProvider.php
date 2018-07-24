<?php


namespace Application\Provider\Logger;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Phalcon\Logger\Formatter\Line;
use Application\Provider\AbstractServiceProvider;
use Application\Adapters\Logger\Sentry;

/**
 * Application\Provider\Logger\ServiceProvider
 *
 * @package Application\Provider\Logger
 */
class ServiceProvider extends AbstractServiceProvider {
	const DEFAULT_LEVEL = 'debug';
	const DEFAULT_FORMAT = '[%date%][%type%] %message%';
	const DEFAULT_DATE = 'd-M-Y H:i:s';

	/**
	 * The Service name.
	 * @var string
	 */
	protected $serviceName = 'logger';

	protected $logLevels = [
		'emergency' => Logger::EMERGENCY,
		'emergence' => Logger::EMERGENCE,
		'critical'  => Logger::CRITICAL,
		'alert'     => Logger::ALERT,
		'error'     => Logger::ERROR,
		'warning'   => Logger::WARNING,
		'notice'    => Logger::NOTICE,
		'info'      => Logger::INFO,
		'debug'     => Logger::DEBUG,
		'custom'    => Logger::CUSTOM,
		'special'   => Logger::SPECIAL,
	];

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function register() {
		$logLevels = $this->logLevels;

		$this->di->set(
			$this->serviceName,
			function ( $filename = null, $format = null ) use ( $logLevels ) {
				$config = container( 'config' )->logger;

				// Setting up the log level
				if ( empty( $config->level ) ) {
					$level = self::DEFAULT_LEVEL;
				} else {
					$level = strtolower( $config->level );
				}

				if ( ! array_key_exists( $level, $logLevels ) ) {
					$level = Logger::DEBUG;
				} else {
					$level = $logLevels[ $level ];
				}

				// Setting up date format
				if ( empty( $config->date ) ) {
					$date = self::DEFAULT_DATE;
				} else {
					$date = $config->date;
				}

				// Format setting up
				if ( empty( $format ) ) {
					if ( ! isset( $config->format ) ) {
						$format = self::DEFAULT_FORMAT;
					} else {
						$format = $config->format;
					}
				}

				// Setting up the filename
				$filename = trim( $filename ?: $config->filename, '\\/' );

				if ( ! strpos( $filename, '.log' ) ) {
					$filename = rtrim( $filename, '.' ) . '.log';
				}

				$logger = new File( rtrim( $config->path, '\\/' ) . DIRECTORY_SEPARATOR . $filename );

				$logger->setFormatter( new Line( $format, $date ) );
				$logger->setLogLevel( $level );

				return $logger;
			}
		);
		$this->di->setShared( 'sentry', new Sentry( container( 'config' ) ) );
	}
}
