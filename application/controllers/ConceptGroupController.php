<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ConceptGroupController extends AuthController{

    private $token_valid;

    protected $access = "*";

    function __construct(){
        parent::__construct();
        $this->load->model('ConceptGroup');
        $this->token_valid = $this->validateToken();
    }

    //Show concept groups
    public function conceptGroups_get(){
        $concepts = $this->ConceptGroup->getConcepts();
        return $this->response($concepts, REST_Controller::HTTP_OK);
    }

}
