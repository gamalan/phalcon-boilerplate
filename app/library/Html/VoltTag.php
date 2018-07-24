<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/4/18
 * Time: 9:39 AM
 */

namespace Application\Html;


use Application\Utils\Translation;
use Phalcon\Tag;

class VoltTag extends Tag {
	public static function _a( $string, array $params = [], $lang = 'en' ) {
		$translation = new Translation();

		return $translation->_a( $string, $params, $lang );
	}

	public static function removeSpaces( $string ) {
		return strtolower( str_replace( ' ', '', $string ) );
	}

	public static function in_array( $needle, $haystack, $strict = false ) {
		if ( in_array( $needle, $haystack, $strict ) ) {
			return true;
		} else {
			return false;
		}
	}
}