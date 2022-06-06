<?php

use Phalcon\Mvc\Controller;

class MyOrdersController extends CommonController
{
    public function initialize()
    {
        $this->loginCheck();
    }

    public function indexAction()
    {
        $this->tag->prependTitle('購入履歴');
        $this->view->setVar('title', $this->session->get("user")["name"]. 'さんの購入履歴リスト');
        $orders = Order::find('customerId=' . $this->session->get("user")["id"]);
        $this->view->setVar('orders', $orders);
        $this->view->setVar('arrayStatus', Status::find());
    }

    public function detailAction()
    {
        $this->tag->prependTitle('注文詳細');
        $order = Order::findFirst([
            'conditions' => 'id=:id:',
            'bind' => ['id' => $this->request->get('orderId')]
        ]);
        if (empty($order) === true) {
            throw new Exception('注文が存在しません。');
        }
        $details = OrderDetail::find([
            'conditions' => 'orderId=:orderId:',
            'bind' => ['orderId' => $this->request->get('orderId')]
        ]);

        $this->view->setVar('title', '受注番号「' . $this->request->get('orderId') . '」の詳細');
        $this->view->setVar('details', $details);
        $this->view->setVar('order', $order);
    }

    public function deleteAction()
    {
        try {
            if ($this->request->get('status') !== 3) {
                $details = OrderDetail::find('orderId=' . $this->request->getPost('orderId'));
                // Start a transaction
                $this->db->begin();

                foreach ($details as $value){
                    $flower = Flower::findFirst('id=' . $value->flowerId);
                    $flower->stock = $flower->stock + $value->quantity;
                    if ($flower->save() === false) {
                        throw new Exception(implode('<br>', $flower->getMessages()));
                    }

                    $detail = OrderDetail::findFirst('id=' . $value->id);
                    $detail->quantity = '0';
                    if ($detail->save() === false) {
                        throw new Exception(implode('<br>', $detail->getMessages()));
                    }
                }

                $order = Order::findFirst('id=' . $this->request->get('orderId'));

                $order->updateTimestamp = date('Y-m-d H:i:s');
                $order->totalPrice = '0';
                $order->status = '3';
                if ($order->save() === false) {
                    throw new Exception(implode('<br>', $order->getMessages()));
                }
                $this->view->success = true;
                $this->db->commit();
                $this->view->message = ['success' => "ご注文を取り消しました。またのご利用お待ちしております。"];

            }
        } catch (Exception $e) {
            $this->db->rollback();
            $this->view->message = ['danger' => '申し訳ございません。正しく処理ができませんでした。もう一度やり直してください。<br>'];
        }
    }
}