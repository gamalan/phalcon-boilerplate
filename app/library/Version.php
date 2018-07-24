<?php


namespace Application;

use Phalcon\Version as PhVersion;

/**
 * Application\Version
 *
 * @package Application
 */
class Version extends PhVersion {
	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 * @codingStandardsIgnoreStart
	 */
	protected static function _getVersion() {
		// @codingStandardsIgnoreEnd
		return [ 1, 0, 0, 0, 0 ];
	}
}
