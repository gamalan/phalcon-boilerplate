<?php

namespace Application\Traits;

use Application\Adapters\Logger\Sentry;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

trait LoggerTrait {
	private $logger;

	public function logDebug( $string ) {
		if ( env( 'APP_DEBUG', false ) ) {
			$logger = container()->getShared( 'logger', [ 'debug.' . container( 'bootstrap' )->getAppMode() ] );
			$logger->debug( $string );
		}
	}

	protected function generateResponse( \Throwable $e ) {
		$exception = $e;

		return sprintf(
			"%s: %s in file %s on line %d%s\n",
			get_class( $exception ),
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine(),
			$exception->getTraceAsString()
		);
	}

	public function sendError( \Throwable $e ) {
		if ( $e instanceof \PDOException ) {
			/** @var \PDOException $e */
			if ( stripos( $e->getMessage(), "server has gone away" ) === false ) {
				$this->sendToSentry( $e );
			}
			$this->sendToLogger( $e );
		} else {
			$this->sendToLogger( $e );
			$this->sendToSentry( $e );
		}
	}

	protected function sendToLogger( \Throwable $e ) {
		if ( container()->has( 'logger' ) ) {
			container( 'logger', [ container( 'bootstrap' )->getAppMode() ] )->error( $this->generateResponse( $e ) );
		}
	}

	protected function sendToSentry( \Throwable $e ) {
		if ( container()->has( 'sentry' ) ) {
			/** @var Sentry $sentry */
			$sentry = container( 'sentry' );
			$sentry->setTag( 'mode', container( 'bootstrap' )->getAppMode() );
			$sentry->logException( $e,
				[],
				$sentry->getLogLevel() );
		}
	}
}
