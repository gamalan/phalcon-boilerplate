<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 6/8/17
 * Time: 9:03 AM
 */

namespace Application\Adapters;


interface ApiInterface
{
    public function createAction();
    public function updateAction($id);
    public function deleteAction($id);
    public function getAllAction();
    public function getByIdAction($id);
}