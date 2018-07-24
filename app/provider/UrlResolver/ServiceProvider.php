<?php


namespace Application\Provider\UrlResolver;

use Phalcon\Mvc\Url;
use Application\Utils\Slug;
use Application\Provider\AbstractServiceProvider;

/**
 * Application\Provider\UrlResolver\ServiceProvider
 *
 * @package Application\Provider\UrlResolver
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The Service name.
	 * @var string
	 */
	protected $serviceName = 'url';

	/**
	 * {@inheritdoc}
	 * The URL component is used to generate all kind of urls in the application.
	 *
	 * @return void
	 */
	public function register() {
		$this->di->setShared(
			$this->serviceName,
			function () {
				$config = container( 'config' );

				$url = new Url();

				if ( ! empty( $config->application->staticBaseUri ) ) {
					$url->setStaticBaseUri( $config->application->staticBaseUri );
				} else {
					$url->setStaticBaseUri( '/' );
				}

				if ( ! empty( $config->application->baseUri ) ) {
					$url->setBaseUri( $config->application->baseUri );
				} else {
					$url->setBaseUri( '/' );
				}

				return $url;
			}
		);

		$this->di->setShared( 'slug', [ 'className' => Slug::class ] );
	}
}
