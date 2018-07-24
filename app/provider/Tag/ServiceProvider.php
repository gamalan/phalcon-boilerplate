<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/6/18
 * Time: 4:54 PM
 */

namespace Application\Provider\Tag;

use Application\Html\VoltTag;
use Application\Provider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The Service name.
	 * @var string
	 */
	protected $serviceName = 'tag';

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function register() {
		$this->di->setShared( $this->serviceName, new VoltTag() );
	}
}