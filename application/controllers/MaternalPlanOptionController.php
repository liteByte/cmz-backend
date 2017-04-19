<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class MaternalPlanOptionController extends AuthController{

    private $token_valid;
    protected $access = "*";

    function __construct(){
        parent::__construct();
        $this->load->model('MaternalPlanOption');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function maternal_plan_option_get(){
        $options = $this->MaternalPlanOption->getMaternalPlanOptions();
        return $this->response($options, RC::HTTP_OK);
    }

}