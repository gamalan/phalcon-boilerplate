<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 3/27/17
 * Time: 7:32 PM
 */

namespace Application\Queue;
use Phalcon\Mvc\User\Component;
use Phalcon\Queue\Beanstalk;

class BasicServer extends AbstractServer
{
    /**
     * Simulates putting a job in the queue
     *
     * @param array $job
     * @return bool
     */
    public function put($job,$tubename=null)
    {
        if(is_null($this->queue)){
            $this->connect();
        }
        try {
            $this->queue->connect();
            $config = $this->component->getDI()->getShared('config');
            $tubename = $this->tube . mt_rand(0, $config->get('beanstalk')->basic_worker_count - 1);
            $this->queue->choose($tubename);
            return $this->queue->put($job);
        }catch (\Throwable $e){
            $this->component->getDI()->getShared('logger')->error($e->getMessage());
        }
    }
}