<?php

use Phalcon\Mvc\Model;

class Customer extends Model
{
    public $id;
    public $name;
    public $postalCode;
    public $prefecture;
    public $address;
    public $email;
    public $password;
    public $createTimestamp;
    
}