<?php


namespace Application\Http\Filter;

use Phalcon\Mvc\User\Component;
use Application\Http\FilterInterface;

/**
 * AJAX HTTP filter
 *
 * @package Application\Http\Filter
 */
class Ajax extends Component implements FilterInterface
{
    /**
     * Check if the request was made with Ajax
     *
     * @return bool
     */
    public function check()
    {
        $request = $this->di->getShared('request');

        return $request->isAjax();
    }
}
