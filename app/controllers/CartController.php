<?php

use Phalcon\Mvc\Controller;

class CartController extends \Phalcon\MVC\Controller
{
    protected array $items = [];

    public function ___construct()
    {
        $this->items = [];
    
    }

    public function indexAction()
    {
        $this->view->setVar('title', 'Shopping Cart');

        if (empty($this->request->getPost('id')) === false || empty($this->request->getPost('quantity')) === false ) {
            $id = $this->request->getPost('id');
            $quantity = $this->request->getPost('quantity');
    
            if ($this->session->has("cart") ) {
            
                $this->setItems($this->session->get("cart"));
                $this->add($id, $quantity);
                $this->session->set("cart",  $this->getItems());
            
            } else {
                $this->add((int)$id, (int)$quantity);
                $this->session->set("cart", $this->getItems());
            }
        }

        $flowers = Flower::find();
        $this->view->setVar('flowers', $flowers);
    }

    public function setItems(array $data)
    {
        $this->items = $data;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getIndex(int $itemId): int
    {
        foreach ($this->items as $key => $data) {
             if ($data['id'] === $itemId) {
                return $key;
            }
        }
        return -1;
    }

    public function add(int $itemId, int $quantity)
    {
        $key = $this->getIndex($itemId);
        if ($key >= 0) {
            $data = $this->items[$key];
        } else {
            $data = [
                'id' => $itemId,
                'quantity' => 0,
            ];
        }
        $data['quantity'] += $quantity;
        if ($key < 0) {
            $this->items[] = $data;
        } else {
            $this->items[$key] = $data;
        }
    }


    public function removeAction()
    {
        if (empty($this->request->getPost('id')) === false) {
            $id = $this->request->getPost('id');
            $key = $this->getIndex((int)$id);

            if ($key >= 0) {
                unset($this->items[$key]);
                $this->items = array_values($this->items);
                $message = "削除できました。";
            } else {
                $message = '商品番号取得に失敗しました';
            } 
            $this->view->message = $message;
        }
    }
}

