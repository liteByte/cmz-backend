<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class BillingCodeController extends AuthController{

    private $token_valid;
    protected $access = "*";

    function __construct(){
        parent::__construct();
        $this->load->model('BillingCode');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function billing_code_get(){
        $codes = $this->BillingCode->getBillingCodes();
        return $this->response($codes, RC::HTTP_OK);
    }

}