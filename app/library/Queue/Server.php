<?php


namespace Application\Queue;

/**
 * Server
 *
 * Facade to Phalcon\Queue\Beanstalkd
 */
class Server extends AbstractServer
{
    public function getPreProcessSleep(){
        $config =  $this->component->getDI()->getShared('config');
        return $config->get('beanstalk')->preprocess_sleep;
    }
}
