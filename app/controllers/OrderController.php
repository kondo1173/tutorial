<?php

use Phalcon\Mvc\Controller;

class OrderController extends \Phalcon\MVC\Controller
{
    public function indexAction()
    {
        $this->view->setVar('title', '送付先登録フォーム');

        $prefecture = Prefecture::find();
        $this->view->setVar('prefecture', $prefecture);

        $user = $this->session->get("user");
 
        $userData = Customer::findFirst('id=' . $user['id']);
        $this->view->userData =$userData;
        $totalPrice = $this->request->getPost('totalPrice');
        $this->view->totalPrice = $totalPrice;
    }


    public function registerAction()
    {
        $order = new Order();

        //assign value from the form to $order
        $user->assign(
            $this->request->getPost(),
            [
                'id',
                'customerId' => $customerId,
                'createTimestamp' => 'NULL',
                'updateTimestamp' => 'NULL',
                'totalPrice' => $totalPrice,
                'name' => $name,
                'postalCode',
                'prefecture',
                'address',
                'status' => '1'
            ]
        );

        // Store and check for errors
        $success = $user->save();

        // passing the result to the view
        $this->view->success = $success;

        if ($success) {
            $message = "Thanks for registering!";
        } else {
            $message = "Sorry, the following problems were generated:<br>"
                     . implode('<br>', $user->getMessages());
        }

        // passing a message to the view
        $this->view->message = $message;
    }
}
