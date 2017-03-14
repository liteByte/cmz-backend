<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class CategoryCircleController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('CategoryCircle');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function category_circle_get(){

        //Validates Token
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        $circle = $this->CategoryCircle->getCategoryCircle();
        return $this->response($circle, RC::HTTP_OK);
    }

}