<?php

return [
	'path' => dirname( __DIR__ ) . '/storage/logs',

	'format' => env( 'LOGGER_FORMAT', '[%date%][%type%] %message%' ),

	'date' => 'd-M-Y H:i:s',

	'level' => env( 'LOGGER_LEVEL', 'error' ),

	'filename' => env( 'LOGGER_DEFAULT_FILENAME', 'application' ),
];
