<?php



namespace Application\Controllers;

use Application\Utils\StringHelper;
use Phalcon\Error\Error;
use Phalcon\Logger;
use Ramsey\Uuid\Uuid;

/**
 * Error Controller
 *
 * @package Application\Controllers
 */
class ErrorController extends ControllerBase {
	public function initialize() {
		$this->view->setVars( [
			'title'   => 'Error',
			'support' => container( 'config' )->site->support,
			'noindex' => true,
			'errorid' => container( 'sentry' )->getLastEventId()
		] );

		$this->view->setMainView( 'frontend/index' );
	}

	public function route400Action() {
		$this->createError(
			'Bad request',
			400,
			'Something is not quite right.',
			__LINE__
		);
	}

	public function route401Action() {
		$this->response->setHeader( 'WWW-Authenticate', 'Digest realm="Access denied"' );

		$this->createError(
			'Authorization required',
			401,
			'To access the requested resource requires authentication.',
			__LINE__
		);
	}

	public function route403Action() {
		$this->createError(
			'Access is denied',
			403,
			'Access to this resource is denied by the administrator.',
			__LINE__
		);
	}

	public function route404Action() {
		$this->createError(
			'Page not found',
			404,
			"Sorry! We can't seem to find the page you're looking for.",
			__LINE__
		);
	}

	public function route500Action() {
		$this->response->setHeader( 'Retry-After', 3600 );

		$this->createError(
			'Something is not quite right',
			500,
			'We&rsquo;ll be back soon!',
			__LINE__
		);
	}

	public function route503Action() {
		$this->createError(
			'Site Maintenance',
			503,
			'Unfortunately an unexpected system error occurred.',
			__LINE__
		);
	}

	protected function createError( $title, $code, $message, $line ) {
		$this->tag->setTitle( $title );
		$this->response->setStatusCode( $code );

		$this->view->setVars( [
			'code'    => $code,
			'message' => $message,
		] );
		$this->view->pick( 'error/route404' );
	}
}
