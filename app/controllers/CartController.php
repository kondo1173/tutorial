<?php

use Phalcon\Mvc\Controller;

class CartController extends CommonController
{

    public function indexAction()
    {
        $this->view->setVar('title', 'Shopping Cart');
        $this->loginCheck();

        if ($this->request->isPost()) {
            $id = $this->request->getPost('id');
            $quantity = $this->request->getPost('quantity');
    
            if ($this->session->has("cart")) {
                $cart = $this->session->get("cart");
                $arrayId = array_column($cart, 'id');
                if (!in_array($id, $arrayId)) {
                    $addData = array (
                        'id' => (int)$id,
                        'quantity' => (int)$quantity
                    );
                    array_push($cart, $addData);
                    $this->session->set('cart', $cart);
                } else {
                    $index = array_search($id, $arrayId);
                    $cart[$index]["quantity"] += $quantity;
                    $this->session->set("cart", $cart);
                }
            } else {
                $cart[] = array (
                    'id' => (int)$id,
                    'quantity' => (int)$quantity
                );
                $this->session->set('cart', $cart);
            }
        }
        $flowers = Flower::find();
        $this->view->setVar('flowers', $flowers);
    }

    public function removeAction()
    {
        $cart =$this->session->get("cart");
        $id = $this->request->getPost('id');

        foreach($cart as $key => $value){
            if ($value['id'] == $id) {
                unset($cart[$key]);
                $cart = array_values($cart);
                $this->session->set("cart", $cart);
                $message = "削除できました。";
                break;
            } else {
                $message = '商品番号取得に失敗しました';
            }
        } 
        $this->view->message = $message;
    }

    public function deleteAction()
    {
        $this->session->remove("cart");
        return $this->dispatcher->forward(
            [
                "controller" => "index",
                "action"     => "index",
            ]
        );
    }
}