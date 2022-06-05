<?php
use Phalcon\Mvc\Controller;

class CommonController extends \Phalcon\MVC\Controller
{
    public function loginCheck()
    {
        if (empty($this->session->has('user')) === true) {
            return $this->dispatcher->forward(
                [
                    "controller" => "login",
                    "action"     => "index",
                ]
            );
        }
    }

    public function cartCheck()
    {
        if (empty($this->session->has('cart')) === true) {
            return $this->dispatcher->forward(
                [
                    "controller" => "index",
                    "action"     => "index",
                ]
            );
        } 
    }
}