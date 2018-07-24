<?php


namespace Application\Http;

/**
 * HTTP Filter Interface
 *
 * @package Application\Http
 */
interface FilterInterface
{
    /**
     * @return boolean
     */
    public function check();
}
