<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class PaymentTypesController extends AuthController{

    private $token_valid;
    protected $access = "*";
    function __construct(){
        parent::__construct();
        $this->load->model('PaymentTypes');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function payment_types_get(){
        $circle = $this->PaymentTypes->getPaymentTypes();
        return $this->response($circle, RC::HTTP_OK);
    }

}