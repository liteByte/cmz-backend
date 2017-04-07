<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class InternmentAmbulatoryOptionController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('InternmentAmbulatoryOption');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function internment_ambulatory_option_get(){

        //Validates Token
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        $options = $this->InternmentAmbulatoryOption->getInternmentAmbulatoryOptions();
        return $this->response($options, RC::HTTP_OK);
    }

}