<?php

namespace Application\Utils;

class CustomUtil {

	function removeSpaces($string) {
		return strtolower(str_replace(' ', '', $string));
	}

	function in_array($needle, $haystack) {
		if (in_array($needle, $haystack)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
