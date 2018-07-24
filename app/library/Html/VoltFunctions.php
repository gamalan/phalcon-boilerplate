<?php


namespace Application\Html;


/**
 * Application\Provider\VoltTemplate\VoltFunctions
 *
 * @package Application\Provider\VoltTemplate
 */
class VoltFunctions {
	/**
	 * Compile any function call in a template.
	 *
	 * @param string $name
	 * @param mixed $arguments
	 *
	 * @return null|string
	 */
	public function compileFunction( $name, $arguments ) {
		switch ( $name ) {
			case 'join':
				return 'implode(' . $arguments . ')';
			case 'chr':
			case 'number_format':
				return $name . '(' . $arguments . ')';
			case 'btoa':
				return 'base64_decode(' . $arguments . ')';
			case 'atob':
				return 'base64_encode(' . $arguments . ')';
			case 'intval':
				return 'intval(' . $arguments . ')';
			case 'strlen':
				return 'strlen(' . $arguments . ')';
		}

		return null;
	}

	/**
	 * Compile some filters.
	 *
	 * @param  string $name The filter name
	 * @param  mixed $arguments The filter args
	 *
	 * @return string|null
	 */
	public function compileFilter( $name, $arguments ) {
		switch ( $name ) {
			// @todo
		}

		return null;
	}
}
