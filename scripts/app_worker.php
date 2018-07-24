<?php
require __DIR__ . '/cli-bootstrap.php';

use Application\Utils\WorkerFunction;
use Application\Utils\Console;
use alphayax\utils\cli\GetOpt;
use Application\Queue\BasicServer;

set_time_limit( 0 );
$console             = new Console();
$config              = $console->getConfig();
$tubename            = $config->get( 'beanstalk' )->basic_worker_name;
$Args                = GetOpt::getInstance();
$opt_t               = $Args->addShortOpt( 't', 'tube number', true, true );
$Args->parse();
$worker = new WorkerFunction();
$value  = $opt_t->getValue();
$value  = strlen( $value ) > 0 ? $value : "0";
$worker->{"echoHello"}( "Kirim.Email " . $tubename . $value );
$server = new BasicServer( $tubename . $value );

while ( true ) {
	if ( $job = $server->reserve() ) {
		if ( $console->isDbActive() ) {
			$console->connectDB();
			$jobBody = $job->getBody();
			try {
				if ( method_exists( $worker, $jobBody['function_name'] ) ) {
					$worker->{$jobBody['function_name']}( $jobBody['params'], $job );
				} else {
					$job->delete();
				}
			} catch ( Exception $exc ) {
				echo $exc->getTraceAsString();
				$job->bury();
			}
		} else {
			echo "Release";
			$job->release();
			$console->connectDB();
			echo $console->isDbActive();
		}
		//$server->kick();
	} else {
		echo "Sleep";
		if ( is_object( $job ) ) {
			$job->release();
		}
		//$server->kick();
		sleep( $server->getProcessSleep() );
	}
}