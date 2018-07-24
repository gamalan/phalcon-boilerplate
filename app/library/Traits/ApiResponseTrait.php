<?php

/**
 * User: rizts
 * Date: 05/04/17
 * Time: 12:45
 */

namespace Application\Traits;

trait ApiResponseTrait
{
    // Call this func to set json response enabled
    public function setJsonResponse()
    {
        $this->view->disable();

        $this->_isJsonResponse = true;
        $this->response->setContentType('application/json', 'UTF-8');
    }

    public function setXMLResponse()
    {
        $this->view->disable();

        $this->_isJsonResponse = false;
        $this->response->setContentType('text/xml', 'UTF-8');
    }

    protected function setForbidden()
    {
        $this->response->setStatusCode(403);
        $this->code_val = 403;
    }

    protected function setUnauthorized()
    {
        $this->response->setStatusCode(401);
        $this->code_val = 401;
    }

    protected function setBadrequest()
    {
        $this->response->setStatusCode(400);
        $this->code_val = 400;
    }

    protected function setNotfound()
    {
        $this->response->setStatusCode(404);
        $this->code_val = 404;
    }

    protected function setAccepted()
    {
        $this->response->setStatusCode(200);
        $this->code_val = 200;
    }

    protected function setInternalError()
    {
        $this->response->setStatusCode(500);
        $this->code_val = 500;
    }

    // After route executed event
    /**
     * @param $dispatcher Dispatcher
     */
    public function afterExecuteRoute($dispatcher)
    {
        $data = $dispatcher->getReturnedValue();
        if (APPLICATION_ENV != ENV_PRODUCTION) {
            $data['debug'] = $this->data;
            $data['debug-headers'] = $this->request->getHeaders();
        }
        if ($this->_isJsonResponse) {
            if (is_array($data)) {
                $data = json_encode($data);
            }
        } else {
            if (is_array($data)) {
                $data = Array2XML::createXML($data);
            }
        }
        $this->response->setContent($data);
        $this->response->send();
    }

    public function defaultAction()
    {
        $this->setBadrequest();
        return [
            $this->code => $this->code_val,
            "status" => self::ERROR,
            "message" => "Malformed request"
        ];
    }

    protected function finishAPI($returned)
    {
        if (APPLICATION_ENV != ENV_PRODUCTION) {
            $returned['debug'] = $this->data;
            $returned['debug-headers'] = $this->request->getHeaders();
        }
        $this->response->setContent(json_encode($returned));
        $this->response->send();
        die();
    }

    protected function unsetUserdata($array)
    {
        unset($array['is_deleted']);
        unset($array['created_at']);
        unset($array['deleted_at']);
        unset($array['modified_at']);
        unset($array['user_guid']);
        return $array;
    }
}