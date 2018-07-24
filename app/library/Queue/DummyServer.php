<?php


namespace Application\Queue;

/**
 * DummyServer
 *
 * This class replaces Beanstalkd by a dummy server
 */
class DummyServer
{
    /**
     * Simulates putting a job in the queue
     *
     * @param array $job
     * @return bool
     */
    public function put($job)
    {
        return true;
    }
}
