<?php

return [
	'default' => env( 'SESSION_DRIVER', 'redis' ),

	'drivers' => [

		'memcached' => [
			'adapter' => 'Libmemcached',
			'servers' => [
				[
					'host'   => env( 'MEMCACHED_HOST', '127.0.0.1' ),
					'port'   => env( 'MEMCACHED_PORT', 11211 ),
					'weight' => env( 'MEMCACHED_WEIGHT', 100 ),
				]
			],
			// Client options must be instance of array
			'client'  => [

			],
		],

		'redis' => [
			'adapter'    => 'Redis',
			'host'       => env( 'REDIS_HOST', '127.0.0.1' ),
			'port'       => env( 'REDIS_PORT', 6379 ),
			'index'      => env( 'REDIS_INDEX', 0 ),
			'auth'       => env( 'REDIS_PASS', '' ),
			'persistent' => true,
		],

		'file' => [
			'adapter' => 'Files',
		],
	],

	'prefix' => env( 'SESSION_PREFIX', 'application_session' ),

	'uniqueId' => env( 'SESSION_UNIQUE_ID', 'application_' ),

	'lifetime' => env( 'SESSION_LIFETIME', 3600 ),
];
