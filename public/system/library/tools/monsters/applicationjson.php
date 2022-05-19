<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 12.10.19
 * Time: 7:57
 */

namespace Tools\Monsters;

class ApplicationJson
{

    private $_registry;
    public $validator;

    public function __construct(\Registry $registry)
    {
        $this->_registry = $registry;
        $this->validator = new \Validator($this->_registry);
    }

    public function getRequest(): \Request
    {
        return $this->_registry->get('request');
    }

    public function getResponse(): \Response
    {
        return $this->_registry->get('response');
    }

    public function setOutput($json)
    {
        $this->getResponse()->addHeader('Content-Type: application/json; charset: utf8;');
        $this->getResponse()->setOutput(json_encode($json));
    }

}