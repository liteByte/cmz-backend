<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class IvaController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('Iva');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Show roles
    public function iva_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

      $iva = $this->Iva->getIvaTypes();
      return $this->response($iva, REST_Controller::HTTP_OK);
    }

}
