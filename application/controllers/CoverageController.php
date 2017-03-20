<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class CoverageController extends AuthController{

    function __construct(){
        parent::__construct();
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function coverages_get(){
        
    }


}