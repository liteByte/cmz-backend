<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class AffiliateController extends AuthController{

    private $token_valid;
    protected $access = "*";
    function __construct(){
        parent::__construct();
        $this->load->model('Affiliate');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Show affiliates
    public function affiliate_get(){
        $affiliates = $this->Affiliate->getAffiliates();
        return $this->response($affiliates, REST_Controller::HTTP_OK);
    }

}
