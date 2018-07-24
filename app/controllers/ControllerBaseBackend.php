<?php

namespace Application\Controllers;

use Application\Core\Subscriber;
use Application\Models\Users;
use Application\Utils\CustomUtil;
use Application\Core\User;
use Application\Core\UserInfo;
use Application\Core\UserSender;
use Phalcon\Config;

class ControllerBaseBackend extends ControllerBase
{

    public function onConstruct()
    {
        parent::onConstruct();
    }

}
