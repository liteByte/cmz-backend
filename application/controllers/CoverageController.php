<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class CoverageController extends AuthController{

    function __construct(){
        parent::__construct();
        $this->load->model('Coverages');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function coverages_get(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);
        
        if($coverages_result = $this->Coverages->getCoverages())
            return $this->response($coverages_result, RC::HTTP_OK);
        else
            return $this->response(array('error'=>'No hay informacion para mostrar'), RC::HTTP_FORBIDDEN);

    }


    public function getCoverage_get(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        $id = $this->get('id');

        if(empty($id)) return $this->response(array('error'=>'Falta el ID de la Cobertura'), RC::HTTP_BAD_REQUEST);

        if($coverage = $this->Coverages->getCoveragesById($id)){
            return $this->response($coverage, RC::HTTP_OK);
        }else{
            return $this->response(array('error'=>'Numero de cobertura no encontrado'), RC::HTTP_FORBIDDEN);

        }
    }



    public function removeCoverage_delete(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        $id = $this->get('id');
        
        if(!$this->Coverages->delete($id, $this->token_valid->user_id)){
            return $this->response(array('msg'=>'Error al intentar eliminar Cobertura'), RC::HTTP_OK);
        }else{
            return $this->response(array('msg'=>'Cobertura eliminada satisfactoriamente'), RC::HTTP_OK);
        }
    }
}