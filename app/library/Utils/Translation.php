<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/4/18
 * Time: 8:40 AM
 */

namespace Application\Utils;


use Phalcon\Translate\Adapter\NativeArray;

class Translation {
	/**
	 * @param string $lang
	 *
	 * @return NativeArray
	 */
	public function getTranslation( $lang = 'en' ) {
		$path = BASE_DIR . '/app/messages/' . $lang . '.php';
		if ( file_exists( $path ) ) {
			require $path;
		} else {
			require BASE_DIR . '/app/messages/en.php';
		}

		return new NativeArray( [
			'content' => $messages
		] );
	}


	public function _a( $key, $placeholder = null, $lang = 'en' ) {
		return $this->getTranslation( $lang )->_( $key, $placeholder );
	}
}