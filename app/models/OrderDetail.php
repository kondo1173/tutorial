<?php
use Phalcon\Mvc\Model;

class OrderDetail extends Model
{
    public $id;
    public $orderId;
    public $flowerId;
    public $quantity;
    public $flowerName;
    public $flowerPrice;
    public $createTimestamp;
}
