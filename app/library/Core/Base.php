<?php

namespace Application\Core;


use Application\Queue\BasicServer;
use Application\Traits\DataCleanerTrait;
use Phalcon\Mvc\User\Component;

class Base extends Component
{
    use DataCleanerTrait;
    /** @var  BasicServer $basic_server */
    public $basic_server;
    public function __construct()
    {
        $config = $this->di->getShared('config');
        $this->basic_server = new BasicServer($config->get('beanstalk')->basic_worker_name);
    }

    protected function sha256($string){
        return hash("sha256",$string);
    }
}