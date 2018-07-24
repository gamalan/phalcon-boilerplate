<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 3/27/17
 * Time: 7:34 PM
 */

namespace Application\Queue;

use Phalcon\Mvc\User\Component;
use Phalcon\Queue\Beanstalk;

class AbstractServer
{
    /** @var Component $component */
    protected $component;
    /** @var Beanstalk $queue */
    protected $queue;
    protected $tube;
    protected $config_bean;

    public function __construct($tube, $host = 'localhost', $port = '11300')
    {
        $this->component = new Component();
        try {
            $config = $this->component->getDI()->getShared('config');
            $this->config_bean = [
                "host" => $config->get('beanstalk')->host,
                "port" => $config->get('beanstalk')->port,
            ];
        } catch (\Throwable $exc) {
            $this->component->getDI()->getShared('sentry')->logException($exc);
            $this->config_bean = [
                "host" => $host,
                "port" => $port,
            ];
        }
        $this->tube = $tube;
        $this->queue = null;
    }

    protected function connect()
    {
        try {
            $this->queue = new Beanstalk($this->config_bean);
            $this->queue->choose($this->tube);
            $this->queue->watch($this->tube);
        } catch (\Throwable $exc) {
            $this->component->getDI()->getShared('sentry')->logException($exc);
        }
    }

    /**
     * Simulates putting a job in the queue
     *
     * @param array $job
     * @return bool
     */
    public function put($job, $tubename = null)
    {
        if(is_null($this->queue)){
            $this->connect();
        }
        try {
            $this->queue->connect();
            if ($tubename != null) {
                $this->queue->choose($tubename);
            }
            $this->queue->put($job);
        } catch (\Throwable $e) {
            $this->component->getDI()->getShared('logger')->error($e->getMessage());
        }
    }

    public function reserve()
    {
        if(is_null($this->queue)){
            $this->connect();
        }
        return $this->queue->reserve();
    }

    public function peekReady()
    {
        if(is_null($this->queue)){
            $this->connect();
        }
        return $this->queue->peekReady();
    }

    public function getActiveTubeStats()
    {
        if(is_null($this->queue)){
            $this->connect();
        }
        return $this->queue->statsTube($this->tube);
    }

    public function getProcessSleep()
    {
        $config = $this->component->getDI()->getShared('config');
        return $config->get('beanstalk')->process_sleep;
    }

    public function kick(){
        return $this->queue->kick(100);
    }
}