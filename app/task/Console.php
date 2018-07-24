<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/9/18
 * Time: 6:28 AM
 */

namespace Application\Task;


use Application\Console\AbstractTask;
use Psy\Shell;

class Console extends AbstractTask {

	/**
	 * @Doc("Get application console")
	 */
	public function main() {
		$this->output( sprintf( '%s %s', container( 'app' )->getName(), container( 'app' )->getVersion() ) );
		$shell = new Shell();
		$shell->run();
	}
}