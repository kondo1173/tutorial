<?php

use Phalcon\Mvc\Controller;

class IndexController extends CommonController
{
    public function indexAction()
    {
        $this->tag->prependTitle('Flower List');
        $this->view->setVar('title', 'Flower List');
        $this->loginCheck();

        $this->view->setVar('flowers', Flower::find());
        $this->view->setVar('categories', Category::find());
        $this->view->setVar('colors', Color::find());
        $this->view->setVar('stocks', Stock::find());
        $this->view->setVar('prices', Price::find());
    }

    public function searchAction()
    {
        $this->view->setVar('title', '検索結果');
        $this->loginCheck();
    
        $this->session->set("category", $this->request->getPost('category'));
        $this->session->set("color", $this->request->getPost('color'));
        $this->session->set("price", $this->request->getPost('price'));
        $this->session->set("stock", $this->request->getPost('stock'));

        $search = Flower::query();
        if (empty($this->session->get('category')) === false) {
            $search->where('category = :category:', ['category' => $this->session->get('category')]);
        }

        $color = $this->session->get('color');
        if (is_array($color) === true && count($color) > 0) {
            $search->inWhere('color', $color);
        }

        if (empty($this->session->get('price')) === false) {
            if ($this->session->get('price') == 1) {
                $search->andWhere('price <= 2000');
            } elseif ($this->session->get('price') == 2) {
                $search->betweenWhere('price', 2000, 4000);
            } else {
                $search->andWhere('price >= 4000');
            }
        }

        if (empty($this->session->get('stock')) === false) {
            if ($this->session->get('stock') != 4) {
                $stock = $this->session->get('stock');
                $search->andWhere('stock=:stock:', ['stock' => $stock - 1]);
            } else {
                $search->andWhere('stock >= 3');
            }
        }

        $result = $search->execute();
        $this->view->setVar('result', $result);
        $this->view->setVar('categories', Category::find());
        $this->view->setVar('colors', Color::find());
        $this->view->setVar('stocks', Stock::find());
        $this->view->setVar('prices', Price::find());
    }

    public function clearAction()
    {
        $this->session->remove("category");
        $this->session->remove("color");
        $this->session->remove("price");
        $this->session->remove("stock");

        return $this->dispatcher->forward(
            [
                "controller" => "index",
                "action"     => "index",
            ]
        );
    }
}