<?php

return [

	// smtp, sendmail, mail
	'driver' => env( 'EMAIL_DRIVER', 'smtp' ),

	'host' => env( 'EMAIL_HOST' ),

	'port' => env( 'EMAIL_PORT' ),

	'security'   => env( 'EMAIL_ENCRYPTION' ),

	'encryption' => env( 'EMAIL_ENCRYPTION' ),

	'username' => env( 'EMAIL_USERNAME' ),

	'password' => env( 'EMAIL_PASSWORD' ),

	'ssl_option' => [
		'allow_self_signed' => env( 'EMAIL_SSLOPTIONS_ALLOW_SELF_SIGNED' ),
		'verify_peer'       => env( 'EMAIL_SSLOPTIONS_VERIFY_PEER' ),
		'verify_peer_name'  => env( 'EMAIL_SSLOPTIONS_VERIFY_PEER_NAME' ),
	],

	'fromEmail' => env( 'EMAIL_FROM_ADDRESS', 'postmaster@app.vm' ),
	'fromName'  => env( 'APP_NAME', 'App.Vm' ),

	'sendmail' => '/usr/sbin/sendmail -bs',
];
