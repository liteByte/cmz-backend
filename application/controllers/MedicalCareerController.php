<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class MedicalCareerController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('MedicalCareer');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function medical_career_get(){

        //Validates Token
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        $circle = $this->MedicalCareer->getMedicalCareer();
        return $this->response($circle, RC::HTTP_OK);
    }

}