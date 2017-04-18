<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ScopeController extends AuthController{

    private $token_valid;
    protected $access = "*";
    function __construct(){
        parent::__construct();
        $this->load->model('Scope');
        $this->token_valid = $this->validateToken();
    }

    //Show roles
    public function scopes_get(){
      $iva = $this->Scope->getScopes();
      return $this->response($iva, REST_Controller::HTTP_OK);
    }

}
