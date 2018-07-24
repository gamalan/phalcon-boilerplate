<?php

/*
 +------------------------------------------------------------------------+
 | Kirimemail                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2013-2016 Phalcon Team and contributors                  |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file LICENSE.txt.                             |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalconphp.com so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
*/

namespace Application\Listener;

use Phalcon\Events\Event;
use Phalcon\Db\Adapter\Pdo;

/**
 * Application\Listener\Database
 *
 * @package Application\Listener
 */
class Database {
	/**
	 * Database queries listener.
	 *
	 * You can disable queries logging by changing log level.
	 *
	 * @param  Event $event
	 * @param  Pdo $connection
	 *
	 * @return bool
	 */
	public function beforeQuery( Event $event, Pdo $connection ) {
		$string    = $connection->getSQLStatement();
		$variables = $connection->getSqlVariables();
		$context   = $variables ?: [];

		$em = container('eventsManager');

        $connection->setEventsManager( new $em );
        $now    = new \DateTime();
        $mins   = $now->getOffset() / 60;
        $sgn    = ($mins < 0 ? -1 : 1);
        $mins   = abs($mins);
        $hrs    = floor($mins / 60);
        $mins   -= $hrs * 60;
        $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
        $connection->execute( "SET time_zone = '{$offset}';" );
        $connection->setEventsManager( $em );

		$logger = container()->get( 'logger', [ 'db' ] );

		if ( ! empty( $context ) ) {
			$context = ' [' . implode( ', ', $context ) . ']';
		} else {
			$context = '';
		}

		$logger->debug( $string . $context );

		return true;
	}
}
