<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class AuthController extends REST_Controller {

    function __construct() {
        parent::__construct();
        if(!$this->validateToken()) {
            $this->no_valid();
        }
    }

    public function validateToken() {
        $headers = apache_request_headers();
        $headers = array_change_key_case($headers, CASE_LOWER);
        $token = $headers["authorization"];


        if (!empty($token)) {
            try {
                $user = JWT::decode($token);
            }catch (Exception $e) {
                $token_valid = new stdClass();
                $token_valid->status = "error";
                $token_valid->message = "Token de autenticación no válido";
                return $token_valid;
            }
//            print_r($this->access);
//            die("end");
            if($this->access != "*"){
                $result = in_array($this->access, $user->permissions);
                if(!$result)   return false;
            }

            return $user;
        }else {
            $token_valid = new stdClass();
            $token_valid->status = "error";
            $token_valid->message = "Token de autenticación no enviado";
            return $token_valid;
        }
    }

    public function no_valid(){
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        header("HTTP/1.1 404 Internal Server Error");
        $message = "No tienes los permisos para realizar esta acción";
        echo json_encode(
            array(
                'message' => $message
            )
        );
        exit;
    }
}



