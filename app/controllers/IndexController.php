<?php

use Phalcon\Mvc\Controller;

class IndexController extends \Phalcon\MVC\Controller
{
    public function indexAction()
    {
        $this->view->setVar('title', 'Flower List');
    
        // if (empty($this->session->has('user')) === true) {
        //     return $this->dispatcher->forward(
        //         [
        //             "controller" => "login",
        //             "action"     => "index",
        //         ]
        //     );
        // } 

        
        $flowers = Flower::find();
        $this->view->setVar('flowers', $flowers);
    }
}