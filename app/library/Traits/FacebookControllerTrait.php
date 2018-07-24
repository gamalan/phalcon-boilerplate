<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/14/17
 * Time: 6:37 AM
 */

namespace Application\Traits;


use Application\Core\FacebookUser;

trait FacebookControllerTrait
{
    public function isFacebookConnected($guid){
        $fb_user = new FacebookUser();
        return is_string($fb_user->getFbAccessCode($guid));
    }
}