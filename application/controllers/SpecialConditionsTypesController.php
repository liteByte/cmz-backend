<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class SpecialConditionsTypesController extends AuthController{

    protected $access = "ABMcondicionesespeciales";
    function __construct(){
        parent::__construct();
        $this->load->model('SpecialConditionsTypes');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function types_get(){
        $types = $this->SpecialConditionsTypes->gettypes();
        return $this->response($types, RC::HTTP_OK);
    }
}