<?php


namespace Application\Provider;

use Phalcon\Di\InjectionAwareInterface;

/**
 * Application\Provider\ServiceProviderInterface
 *
 * @package Application\Provider
 */
interface ServiceProviderInterface extends InjectionAwareInterface
{
    /**
     * Register application service.
     *
     * @return void
     */
    public function register();

    /**
     * Package boot method.
     *
     * @return void
     */
    public function boot();

    /**
     * Configures the current service provider.
     *
     * @return void
     */
    public function configure();
    /**
     * Get the Service name.
     *
     * @return string
     */
    public function getName();
}
