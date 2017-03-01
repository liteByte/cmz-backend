<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class AuthController extends REST_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('login_model');
    }

    public function validateToken($headers){

        $token = $headers["Authorization"];
        if(!empty($token)){
            $user = JWT::decode($token);
            print_r($user);
            $dni = $user->document_number;
            if($dni){
                $user_data = $this->login_model->getUser($dni);
                if(!$user_data){
                    return  $this->set_response([
                        'message' => 'Token No valido'
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    exit;
                }else{
                    return $user_data;
                }
            }else{
                return  $this->set_response([
                    'message' => 'Token No valido'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

        }else{

            return  $this->set_response([
                'message' => 'Token No valido'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

    }
}