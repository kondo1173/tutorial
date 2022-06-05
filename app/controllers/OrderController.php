<?php

use Phalcon\Mvc\Controller;

class OrderController extends CommonController
{
    public function indexAction()
    {
        $this->view->setVar('title', '送付先登録フォーム');
        $this->loginCheck();
        $this->cartCheck();

        $this->view->setVar('prefecture', Prefecture::find());

        $user = $this->session->get("user");
 
        $userData = Customer::findFirst('id=' . $user['id']);
        $this->view->userData = $userData;
        $totalPrice = $this->request->getPost('totalPrice');
        $this->view->totalPrice = $totalPrice;
    }


    public function registerAction()
    {
        $this->view->setVar('title', '最終確認画面');

        if ($this->session->has("cart")) {
            $order = new Order();
            // Start a transaction
            $this->db->begin();
            //注文書作成
            $order->id = 'NULL';
            $order->customerId = $this->session->get("user")['id'];
            $order->updateTimestamp = 'NULL';
            $order->totalPrice = $this->request->getPost('totalPrice');
            $order->name = $this->request->getPost('name');
            $order->postalCode = $this->request->getPost('postalCode');
            $order->prefecture = $this->request->getPost('prefecture');
            $order->address = $this->request->getPost('address');
            $order->status = '1';
            if ($order->save() === false) {
                $this->db->rollback();
                return;
            }
            $order->id = $order->getWriteConnection()->lastInsertId();
            $data = Order::findFirst('id=' . $order->id);
            $this->view->setVar('data', $data);

            //在庫から数量引き出す
            foreach ($this->session->get("cart") as $key => $value) {
                $flower = Flower::findFirst("id=" . $value['id']);
                $flower->stock = $flower->stock - $value['quantity'];

                if ($flower->stock >= 0 || $flower->stock >= $value['quantity']) {
                    $flower = Flower::find(array('id=' . $value['id']))->getFirst();
                    $flower->save();
                } else {
                    return $this->dispatcher->forward(
                        [
                            "controller" => "cart",
                            "action"     => "index",
                        ]
                    );
                }

                //注文詳細テーブル
                $details =new OrderDetail();
                $details->id = 'NULL';
                $details->orderId = $order->id;
                $details->flowerId = $value['id'];
                $details->quantity = $value['quantity'];
                $details->flowerName = $flower->name;
                $details->flowerPrice = $flower->price;
                if ($details->save() === false) {
                    $this->db->rollback();
                    return;
                }
                $details = OrderDetail::find('orderId=' . $order->id);
                $this->view->setVar('details', $details);
            }
            // Commit the transaction
            $this->db->commit();
            $this->session->remove('cart');
        } else {
            //購入履歴から購入決済した場合
            $data = Order::findFirst('id=' . $this->request->getPost('orderId'));
            $this->view->setVar('data', $data);
            $details = OrderDetail::find('orderId=' . $this->request->getPost('orderId'));
            $this->view->setVar('details', $details);
        }
    } 


    public function paymentAction()
    {
        try {
            if ($this->request->getPost('status') == 1) {
                $order = Order::findFirst('id=' . $this->request->getPost('orderId'));
                $order->status = '2';
                $success = $order->save(); 
                $this->view->success = $success;
    
            } else {
                throw new Exception('cart');
            }
            if ($success) {
                $message['success'] = "お支払いありがとうございました。";
            } else {
                $message['danger'] = '申し訳ございません。正しく処理ができませんでした。もう一度やり直してください。<br>';
            }
            $this->view->message = $message;
    
        } catch (Exception $e) {
            if ($e->getMessage() == 'cart') {
                return $this->dispatcher->forward(
                    [
                        "controller" => "index",
                        "action"     => "index",
                    ]
                );
            }
            die("エラーメッセージ : {$e->getMessage()}");
        }
    }
}
