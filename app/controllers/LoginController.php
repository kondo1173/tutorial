<?php

use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        $this->view->setVar('title', 'login');
    }

    private function _registerSession($customer)
    {
        $this->session->set(
            "user",
            [
                "id"   => $customer->id,
                "name" => $customer->name,
            ]
        );    
    }

    public function searchAction()
    {
        if ($this->request->isPost()) {
            $email = $this->request->getPost("email");
            $password = $this->request->getPost("password");

            $customer = Customer::findFirst(
                [
                    "email = :email: AND password = :password:",
                    "bind" => [
                        "email" => $email,
                        "password" => $password,
                    ]
                ]
            );

            if (!empty($customer) === true) {
                $this->_registerSession($customer);

                // ユーザーが有効なら、'index' コントローラーに転送する
                return $this->dispatcher->forward(
                [
                    "controller" => "index",
                    "action"     => "index",
                ]
                );

            } else {
                // ログインフォームへ転送
                $errors['pass_err'] = 'Emailもしくはパスワードが一致しません。ご確認の上、もう一度ご入力ください。';
                $this->view->errors = $errors;

                return $this->dispatcher->forward(
                [
                    "controller" => "login",
                    "action"     => "index",
                ]
                );
    
            }
        }
    }
 
}