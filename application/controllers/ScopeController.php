<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ScopeController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('Scope');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Show roles
    public function scopes_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      $iva = $this->Scope->getScopes();
      return $this->response($iva, REST_Controller::HTTP_OK);
    }

}
