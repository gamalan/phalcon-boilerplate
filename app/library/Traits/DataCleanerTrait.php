<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 3/31/17
 * Time: 5:53 AM
 */

namespace Application\Traits;

use voku\helper\AntiXSS;

trait DataCleanerTrait {
	public function cleanDataString( $input, $cleanxss = true ) {
		if ( is_array( $input ) ) {
			$output = [];
			foreach ( $input as $key => $val ) {
				$output[ $key ] = $this->clean_input( $val, $cleanxss );
			}

			return $output;
		} else {
			return $this->clean_input( $input, $cleanxss );
		}
	}

	public function cleanDataStringRecurse( $input, $cleanxss = true ) {
		if ( is_array( $input ) ) {
			$output = [];
			foreach ( $input as $key => $val ) {
				if ( is_array( $val ) ) {
					$output[ $key ] = $this->cleanDataStringRecurse( $val, $cleanxss );
				} else {
					$output[ $key ] = $this->clean_input( $val, $cleanxss );
				}
			}

			return $output;
		} else {
			return $this->clean_input( $input, $cleanxss );
		}
	}

	public function cleanData( $input, $cleanxss = true ) {
		// print_r($input);die();
		if ( is_array( $input ) ) {
			$output = [];
			foreach ( $input as $key => $val ) {
				$output[ $key ] = $this->clean_input_alt( $val, $cleanxss );
			}

			return $output;
		} else {
			return $this->clean_input_alt( $input, $cleanxss );
		}
	}

	private function clean_input_alt( $input, $cleanxss = true, $safe_level = 0 ) {
		$output = $input;
		do {
			$input  = $output;
			$output = $this->strip_tags( $output );
			if ( $cleanxss ) {
				$output = $this->antixss( $output );
			}
			// Use 2nd input param if not empty or '0'
			if ( $safe_level !== 0 ) {
				$output = $this->strip_base64( $output );
			}
		} while ( $output !== $input );

		return $output;
	}

	private function clean_input( $input, $cleanxss = true, $safe_level = 0 ) {
		$output = $input;
		do {
			// Treat $input as buffer on each loop, faster than new var
			$input = $output;
			// Remove unwanted tags
			$output = $this->strip_tags( $output );
			$output = $this->strip_encoded_entities( $output );
			if ( $cleanxss ) {
				$output = $this->antixss( $output );
			}
			// Use 2nd input param if not empty or '0'
			if ( $safe_level !== 0 ) {
				$output = $this->strip_base64( $output );
			}
		} while ( $output !== $input );

		return $output;
	}

	private function strip_encoded_entities( $input ) {
		// Fix &entity\n;
		$input = str_replace( array( '&amp;', '&lt;', '&gt;' ), array( '&amp;amp;', '&amp;lt;', '&amp;gt;' ), $input );
		$input = preg_replace( '/(&#*\w+)[\x00-\x20]+;/u', '$1;', $input );
		$input = preg_replace( '/(&#x*[0-9A-F]+);*/iu', '$1;', $input );
		$input = html_entity_decode( $input, ENT_COMPAT, 'UTF-8' );
		// Remove any attribute starting with "on" or xmlns
		$input = preg_replace( '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+[>\b]?#iu', '$1>', $input );
		// Remove javascript: and vbscript: protocols
		$input = preg_replace( '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $input );
		$input = preg_replace( '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $input );
		$input = preg_replace( '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $input );
		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$input = preg_replace( '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $input );
		$input = preg_replace( '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $input );
		$input = preg_replace( '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $input );

		return $input;
	}

	private function strip_tags( $input ) {
		// Remove tags
		$input = preg_replace( '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $input );
		// Remove namespaced elements
		$input = preg_replace( '#</*\w+:\w[^>]*+>#i', '', $input );

		return $input;
	}

	private function full_trim( $input ) {
		$input = trim( $input, " \t\n\r\0\x0B0\xA0" );

		return $input;
	}

	private function strip_base64( $input ) {
		$decoded = base64_decode( $input );
		$decoded = $this->strip_tags( $decoded );
		$decoded = $this->strip_encoded_entities( $decoded );
		$output  = base64_encode( $decoded );

		return $output;
	}

	private function antixss( $input ) {
		$cleaner = new AntiXSS();

		return $cleaner->xss_clean( $input );
	}
}