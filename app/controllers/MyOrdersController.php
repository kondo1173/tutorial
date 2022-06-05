<?php

use Phalcon\Mvc\Controller;

class MyOrdersController extends CommonController
{
    public function ___construct()
    {
        $this->loginCheck();
    }

    public function indexAction()
    {
        $this->view->setVar('title', $this->session->get("user")["name"]. 'さんの購入履歴リスト');
        $orders = Order::find('customerId=' . $this->session->get("user")["id"]);
        $this->view->setVar('orders', $orders);
        $this->view->setVar('arrayStatus', Status::find());
    }

    public function detailAction()
    {
        $this->view->setVar('title', '受注番号「' . $this->request->getPost('orderId') . '」の詳細');
        $details = OrderDetail::find('orderId=' . $this->request->getPost('orderId'));
        $this->view->setVar('details', $details);
        $order = Order::findFirst('id=' . $this->request->getPost('orderId'));
        $this->view->setVar('order', $order);

    }

    public function deleteAction()
    {


        if ($this->request->get('status') !== 3) {
            $details = OrderDetail::find('orderId=' . $this->request->getPost('orderId'));

            foreach ($details as $value){
                $flower = Flower::findFirst('id=' . $value->flowerId);
                $flower->stock = $flower->stock + $value->quantity;
                $flower->save();

                $detail = OrderDetail::findFirst('id=' . $value->id);
                $detail->quantity = '0';
                $detail->save(); 
            }

            $order = Order::findFirst('id=' . $this->request->get('orderId'));

            $order->updateTimestamp = date('Y-m-d H:i:s');
            $order->totalPrice = '0';
            $order->status = '3';
            $success = $order->save();
            $this->view->success = $success;

            if ($success) {
                $message['success'] = "ご注文を取り消しました。またのご利用お待ちしております。";
            } else {
                $message['danger'] = '申し訳ございません。正しく処理ができませんでした。もう一度やり直してください。<br>';
            }
            $this->view->message = $message;
        }
    }
}