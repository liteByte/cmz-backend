<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class SpecialConditionsTypesController extends AuthController{

    function __construct(){
        parent::__construct();
        $this->load->model('SpecialConditionsTypes');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function types_get(){
        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

        //Validates if the user has permissions to do this action
        if (!in_array("ABMcondicionesespeciales", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta acción'), RC::HTTP_UNAUTHORIZED);

        $types = $this->SpecialConditionsTypes->gettypes();
        return $this->response($types, RC::HTTP_OK);
    }
}