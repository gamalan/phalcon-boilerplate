<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 3/11/17
 * Time: 6:24 PM
 */

namespace Application\Utils;


use Application\Traits\SupervisordDebugTrait;
use Phalcon\Mvc\User\Component;

class Console extends Component
{
    use SupervisordDebugTrait;

    public function getConfig()
    {
        return $this->getDI()->getShared('config');
    }

    public function getDbConfig()
    {
        $config = $this->getConfig();
        $dbConf = $config->get('database')->toArray();
        unset($dbConf['adapter']);
        return $dbConf;
    }

    public function isDbActive()
    {
        try {
            $this->db->getConnectionId();
            $this->db->fetchAll("SELECT 1");
            return true;
        } catch (\Throwable $exc) {
            //$this->di->getShared('sentry')->logException($exc,[],3);
            $this->connectDB();
        }
        return false;
    }

    public function connectDB()
    {
        try {
            $result = $this->db->connect();
            $this->print_d("Database connected ? " . $result);
        } catch (\Throwable $exc) {
            //$this->di->getShared('sentry')->logException($exc,[],3);
            $this->print_a($exc->getTraceAsString());
        }
    }
}