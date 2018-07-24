<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 30/09/16
 * Time: 9:57
 */

namespace Application\Plugin;
use Phalcon\Events\Event;
use Phalcon\Logger;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 */
class NotFoundPlugin extends Plugin
{
    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param MvcDispatcher $dispatcher
     * @param \Throwable $exception
     * @return boolean
     */
    public function beforeException(Event $event, MvcDispatcher $dispatcher, \Throwable $exception)
    {
        error_log($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        error_log($exception->getCode());
        $this->getDI()->getShared('logger')->error($exception->getCode()."-".$exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        $this->getDI()->getShared('sentry')->logException($exception,[],Logger::ERROR);
        if ($exception instanceof DispatcherException) {
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(array(
                        'controller' => 'error',
                        'action' => 'route404',
                        'exception'=> $exception,
                    ));
                    return false;
            }
        }
        $dispatcher->forward(array(
            'controller' => 'error',
            'action'     => 'index',
            'exception'=> $exception,
        ));
        return false;
    }
}