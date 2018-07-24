<?php

return [
	'default' => env( 'DB_ADAPTER', 'mysql' ),

	'drivers' => [

		'mysql' => [
			'adapter'  => 'Mysql',
			'host'     => env( 'DATABASE_HOST', '127.0.0.1' ),
			'dbname'   => env( 'DATABASE_NAME', 'application' ),
			'port'     => env( 'DATABASE_PORT', 3306 ),
			'username' => env( 'DATABASE_USER', 'application' ),
			'password' => env( 'DATABASE_PASS', 'secret' ),
			'charset'  => env( 'DATABASE_CHARSET', 'utf8mb4' ),
		],

		'sqlite' => [
			'adapter' => 'Sqlite',
			'dbname'  => env( 'DATABASE_NAME', app_path( 'application.sqlite' ) ),
		],

		'postgresql' => [
			'adapter'  => 'Postgresql',
			'host'     => env( 'DATABASE_HOST', '127.0.0.1' ),
			'dbname'   => env( 'DATABASE_NAME', 'application' ),
			'port'     => env( 'DATABASE_PORT', 5432 ),
			'username' => env( 'DATABASE_USER', 'application' ),
			'password' => env( 'DATABASE_PASS', 'secret' ),
			'schema'   => env( 'DATABASE_SCHEMA', 'public' ),
		],
	],
];
