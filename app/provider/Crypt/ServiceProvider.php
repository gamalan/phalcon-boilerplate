<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/8/18
 * Time: 2:00 PM
 */

namespace Application\Provider\Crypt;

use Application\Html\VoltTag;
use Application\Provider\AbstractServiceProvider;
use Phalcon\Crypt;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The Service name.
	 * @var string
	 */
	protected $serviceName = 'crypt';

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function register() {
		$this->di->setShared( $this->serviceName, function () {
			$crypt = new Crypt();

			$crypt->setKey( container( 'config' )->crypt->key );

			return $crypt;
		} );
	}
}