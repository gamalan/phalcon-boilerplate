<?php

use Application\Bootstrap;

include_once __DIR__ . '/../bootstrap/autoloader.php';
include_once BASE_DIR . 'app/library/Bootstrap.php';

$bootstrap = new Bootstrap( 'normal', 'app' );

echo $bootstrap->run();

