<?php


namespace Application\Console;

use Application\Version;
use Phalcon\Cli\Console;
use Phalcon\DiInterface;
use Application\Listener\CliInputListener;

/**
 * Application\Console\Application
 *
 * @package Application\Console
 */
class Application extends Console {
	/**
	 * The raw command line argument list.
	 * @var array
	 */
	protected $rawArguments = [];

	/**
	 * Application constructor.
	 *
	 * @param DiInterface $di
	 */
	public function __construct( DiInterface $di ) {
		parent::__construct( $di );

		$this->rawArguments = $_SERVER["argv"];

		$this->setUpListeners();
	}

	/**
	 * Get the application name.
	 *
	 * @return string
	 */
	public function getName() {
		return container( 'config' )->site->software;
	}

	/**
	 * Ghe application version.
	 *
	 * @return string
	 */
	public function getVersion() {
		return Version::get();
	}

	/**
	 * Gets the raw command line argument list.
	 *
	 * @return array
	 */
	public function getRawArguments() {
		return $this->rawArguments;
	}

	/**
	 * Set the cleaned command line arguments.
	 *
	 * @param  array $arguments
	 *
	 * @return $this
	 */
	public function setArguments( array $arguments ) {
		$this->_arguments = $arguments;

		return $this;
	}

	/**
	 * Setting up application listeners
	 */
	protected function setUpListeners() {
		container( 'eventsManager' )->attach( 'console', new CliInputListener() );
	}
}
