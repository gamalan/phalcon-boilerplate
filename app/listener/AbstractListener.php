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

use Phalcon\DiInterface;
use Phalcon\Mvc\User\Component;

/**
 * Application\Listener\AbstractListener
 *
 * @package Application\Listener
 */
class AbstractListener extends Component {
	public function __construct( DiInterface $di = null ) {
		$di = $di ?: container();

		$this->setDI( $di );
	}
}
