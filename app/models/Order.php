<?php
use Phalcon\Mvc\Model;

class Order extends Model
{
    public $id;
    public $customerId;
    public $createTimestamp;
    public $updateTimestamp;
    public $totalPrice;
    public $name;
    public $postalCode;
    public $prefecture;
    public $address;
    public $status;
}
