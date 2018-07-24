<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/9/18
 * Time: 7:22 AM
 */

namespace Application\Task;


use Application\Console\AbstractTask;
use Application\Queue\Server;

class Retry extends AbstractTask {
	/**
	 * @Doc("Retry sending from buried tube")
	 */
	public function sending() {
		try {
			$send_worker_name  = getenv( 'SEND_WORKER_NAME' );
			$send_worker_count = getenv( 'SEND_WORKER_COUNT' );
			$try_kick          = 5000;
			for ( $i = 0; $i < $send_worker_count; $i ++ ) {
				try {
					$this->output( "kick " . $i );
					$sending_queue = new Server( $send_worker_name . $i );
					$sending_queue->kick( $try_kick );
				} catch ( \Throwable $exc ) {
					$this->sendError( $exc );
					$this->output( $exc->getMessage() );
				}
			}
		} catch ( \Throwable $exc ) {
			$this->sendError( $exc );
			$this->output( $exc->getMessage() );
		}
	}

	/**
	 * @Doc("Retry import from buried tube")
	 */
	public function import() {
		try {
			$send_worker_name  = getenv( 'IMPORT_WORKER_NAME' );
			$send_worker_count = getenv( 'IMPORT_WORKER_COUNT' );
			$try_kick          = 5000;
			for ( $i = 0; $i < $send_worker_count; $i ++ ) {
				try {
					$this->output( "kick " . $i );
					$sending_queue = new Server( $send_worker_name . $i );
					$sending_queue->kick( $try_kick );
				} catch ( \Throwable $exc ) {
					$this->sendError( $exc );
					$this->output( $exc->getMessage() );
				}
			}
		} catch ( \Throwable $exc ) {
			$this->sendError( $exc );
			$this->output( $exc->getMessage() );
		}
	}

	/**
	 * @Doc("Retry recipient from buried tube")
	 */
	public function recipient() {
		try {
			$send_worker_name  = getenv( 'CAMPAIGN_RECIPIENT_WORKER_NAME' );
			$send_worker_count = getenv( 'CAMPAIGN_RECIPIENT_WORKER_COUNT' );
			$try_kick          = 5000;
			for ( $i = 0; $i < $send_worker_count; $i ++ ) {
				try {
					$this->output( "kick " . $i );
					$sending_queue = new Server( $send_worker_name . $i );
					$sending_queue->kick( $try_kick );
				} catch ( \Throwable $exc ) {
					$this->sendError( $exc );
					$this->output( $exc->getMessage() );
				}
			}
		} catch ( \Throwable $exc ) {
			$this->sendError( $exc );
			$this->output( $exc->getMessage() );
		}
	}

	/**
	 * @Doc("Retry basic worker from buried tube")
	 */
	public function basic() {
		try {
			$send_worker_name  = getenv( 'WORKER_NAME' );
			$send_worker_count = getenv( 'WORKER_COUNT' );
			$try_kick          = 5000;
			for ( $i = 0; $i < $send_worker_count; $i ++ ) {
				try {
					$this->output( "kick " . $i );
					$sending_queue = new Server( $send_worker_name . $i );
					$sending_queue->kick( $try_kick );
				} catch ( \Throwable $exc ) {
					$this->sendError( $exc );
					$this->output( $exc->getMessage() );
				}
			}
		} catch ( \Throwable $exc ) {
			$this->sendError( $exc );
			$this->output( $exc->getMessage() );
		}
	}
}