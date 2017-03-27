<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ConceptGroupController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('ConceptGroup');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Show concept groups
    public function conceptGroups_get(){

      //Validates if the user is logged and the token sent is valid.

      if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

      $concepts = $this->ConceptGroup->getConcepts();

      return $this->response($concepts, REST_Controller::HTTP_OK);
    }

}
