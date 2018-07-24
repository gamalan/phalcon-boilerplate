<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/6/18
 * Time: 7:53 AM
 */

namespace Application\Exception\Handler;

use Whoops\Handler\Handler;

/**
 * Application\Exception\Handler\ErrorPageHandler
 *
 * @package Application\Error\Handler
 */
class ErrorPageHandler extends Handler {
	/**
	 * {@inheritdoc}
	 *
	 * @return int
	 */
	public function handle() {
		$exception = $this->getException();

		if ( ! $exception instanceof \Exception && ! $exception instanceof \Throwable ) {
			return Handler::DONE;
		}

		if ( ! container()->has( 'view' ) || ! container()->has( 'dispatcher' ) || ! container()->has( 'response' ) ) {
			return Handler::DONE;
		}

		switch ( $exception->getCode() ) {
			case E_WARNING:
			case E_NOTICE:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			case E_STRICT:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			case E_ALL:
				return Handler::DONE;
		}

		$this->renderErrorPage();

		return Handler::QUIT;
	}

	private function renderErrorPage() {
		$config     = container( 'config' )->error;
		$dispatcher = container( 'dispatcher' );
		$view       = container( 'view' );
		$response   = container( 'response' );
		
		$dispatcher->setControllerName( $config->controller );
		$dispatcher->setActionName( $config->action );

		$view->start();
		$dispatcher->dispatch();
		$view->render( $config->controller, $config->action, $dispatcher->getParams() );
		$view->finish();

		$response->setContent( $view->getContent() )->send();
	}
}