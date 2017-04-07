<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class CategoryFemebaController extends AuthController{
    
    private $token_valid;
    protected $access = "*";
    function __construct(){
        parent::__construct();
        $this->load->model('Femeba');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }
    
    public function femeba_get(){
        $femeba = $this->Femeba->getFemeba();
        return $this->response($femeba, RC::HTTP_OK);
    }
}