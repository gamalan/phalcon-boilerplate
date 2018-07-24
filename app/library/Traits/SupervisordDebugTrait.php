<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/21/17
 * Time: 8:17 AM
 */

namespace Application\Traits;


trait SupervisordDebugTrait
{
    public function print_d($string, $tag = 'default')
    {
        if (APPLICATION_ENV != ENV_PRODUCTION) {
            print_r($tag . " : " . var_export($string,true));
            echo "\n";
        }
    }

    public function print_a($string, $tag = 'default')
    {
        print_r($tag . " : " . var_export($string,true));
        echo "\n";
    }
}