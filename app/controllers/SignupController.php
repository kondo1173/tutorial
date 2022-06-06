<?php

use Phalcon\Mvc\Controller;

class SignupController extends CommonController
{
    public function indexAction()
    {
        $this->tag->prependTitle('会員登録');
        $this->view->setVar('title', '新規会員登録');
        $this->view->setVar('prefecture', Prefecture::find());
    }

    public function registerAction()
    {
        $customer = new Customer();
        $password = $this->request->getPost('password');
        $passwordConfirmation = $this->request->getPost('passwordConfirmation');

        if ($password !== $passwordConfirmation) {
            $errors['pass_err'] = 'パスワードが一致しません。もう一度登録しなおしてください。';
            $this->view->errors = $errors;
        } else {

        //assign value from the form to $user
        $customer->assign(
            $this->request->getPost(),
            [
                'name',
                'postalCode',
                'prefecture',
                'address',
                'email',
                'password',
                'createTimestamp'
            ]
        );

        // Store and check for errors
        $success = $customer->save();

        // passing the result to the view
        $this->view->success = $success;

        if ($success) {
            $message['success'] = " Thanks for registering!";
        } else {
            $message['danger'] = " Sorry, the following problems were generated:<br>"
            . implode('<br>', $customer->getMessages());
        }
        // passing a message to the view
        $this->view->message = $message;
        }


    }
}