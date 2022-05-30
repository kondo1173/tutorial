<?php

use Phalcon\Mvc\Controller;

class LogoutController extends Controller
{
    public function indexAction()
    {
        $this->session->destroy();
        return $this->dispatcher->forward(
            [
                "controller" => "Login",
                "action"     => "index",
            ]
        );
    }
}