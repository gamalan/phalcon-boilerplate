<?php

namespace Application\Plugin;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;

/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 30/09/16
 * Time: 9:52
 */
class SecurityPlugin extends Plugin
{
    public function beforeDispatch(){

    }
}