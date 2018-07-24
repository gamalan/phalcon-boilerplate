<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/9/18
 * Time: 6:23 AM
 */

namespace Application\Exception;


class Formatter extends \Phalcon\Logger\Formatter {
	/**
	 * @inheritdoc
	 */
	public function format( $message, $type, $timestamp, $context = null ) {
		return $this->interpolate( $message, $context ?: [] );
	}
}