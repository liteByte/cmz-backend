<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace

use Restserver\Libraries\REST_Controller;


class AuthController extends REST_Controller{

    function __construct(){
        parent::__construct();
    }

    public function validateToken($headers){

        $token = $headers["Authorization"];
        if(!empty($token)){

            try {
                $user = JWT::decode($token);
            } catch (Exception $e) {
                $token_valid = new stdClass();
                $token_valid->status = "error";
                $token_valid->message = "Token de Autenticacion no valido";
                return $token_valid;
            }

                $user->status = "ok";
                return $user;

        }else{

            $token_valid = new stdClass();
            $token_valid->status = "error";
            $token_valid->message = "Token de Autenticacion no enviado";
            return $token_valid;

        }
    }

}
