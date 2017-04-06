<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class IvaController extends AuthController{

    private $token_valid;
    protected $access = "*";
    function __construct(){
        parent::__construct();
        $this->load->model('Iva');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Show roles
    public function iva_get(){
      $iva = $this->Iva->getIvaTypes();
      return $this->response($iva, REST_Controller::HTTP_OK);
    }

}
