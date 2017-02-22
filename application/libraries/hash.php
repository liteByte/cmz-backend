<?php
/**
* Created by PhpStorm.
* User: Edgar
* Date: 22/02/2017
* Time: 12:15
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Hash{
    public function encrypt($data){
        $options = [
            'cost' => 12
        ];
        $hash = password_hash($data, PASSWORD_BCRYPT, $options);
        return $hash;

    }
}

