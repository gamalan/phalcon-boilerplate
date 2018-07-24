<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 1/4/18
 * Time: 5:49 PM
 */

namespace Application\Exception;

use Exception;

class PDODbException extends \PDOException {

	public function __construct( $message = "", $code = 0, \PDOException $e = null ) {
		// in case they call: new MyException($somePDOException);
		// instead of following the interface.
		//
		if ( is_subclass_of( $message, PDOException ) ) {
			$e       = $message;
			$code    = $e->getCode();
			$message = $e->getMessage();
		}

		// Let PDOException do its normal thing
		//
		parent::__construct( $message, $code, $e );

		// Now to correct the code number.
		//
		$state = $this->getMessage();
		if ( ! strstr( $state, 'SQLSTATE[' ) ) {
			$state = $this->getCode();
		}
		if ( strstr( $state, 'SQLSTATE[' ) ) {
			preg_match( '/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $state, $matches );
			$this->code    = ( $matches[1] == 'HT000' ? $matches[2] : $matches[1] );
			$this->message = $matches[3];
		}
	}
}