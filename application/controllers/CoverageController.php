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
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        
    }


}