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

    public function coverages_post(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        $unit_default = ["A", "B", "E", "G", "P", "Q", "R", "V"];

        $post = json_decode(file_get_contents('php://input'));

        $plan_id                    = $post->plan_id;
        $medical_insurance_id       = $post->medical_insurance_id;
        $data                       = $post->data;
        if(empty($plan_id))                     return $this->response(array('error'=>'No se ha ingresado el id del Plan'), RC::HTTP_BAD_REQUEST);
        if(empty($medical_insurance_id))        return $this->response(array('error'=>'No se ha ingresado el id de la Obra Social'), RC::HTTP_BAD_REQUEST);

        // Range can be 0-100, 2 decimal
        foreach ($data as $h_g){
            $validate_honorary      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $h_g->honorary);
            $validate_expenses      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $h_g->expense);
            if(!$validate_honorary || !$validate_expenses){
                return $this->response(array('error'=>'Error, verifique el valor de Honorarios y/o Gastos'), RC::HTTP_BAD_REQUEST);
            }
        }

        //Validate if exist all units
        foreach ($data as $units){
            $type_units = $units->type_units;
            if($type_units == "Ambulatorio"){ $unit_ambulatorio[] =  $units->units;}
            if($type_units == "Internacion"){ $unit_internacion[] =  $units->units;}
        }

        $units_default_ambulatorio = array_values(array_diff($unit_default, $unit_ambulatorio));
        $units_default_internacion = array_values(array_diff($unit_default, $unit_internacion));

        foreach ($units_default_ambulatorio as $u){
            $default_data = [
                "type_units"=> "Ambulatorio",
                "units"=> $u,
                "honorary"=> 100.00,
                "expense"=>  100.00
            ];
            array_push($data, (object)$default_data);
        }
        foreach ($units_default_internacion as $u){
            $default_data = [
                "type_units"=> "Internacion",
                "units"=> $u,
                "honorary"=> 100.00,
                "expense"=>  100.00
            ];
            array_push($data, (object)$default_data);
        }

        //Save Data
        $result = $this->Coverages->save($plan_id, $medical_insurance_id,$data);

        if($result != 1 ){
            if(strcmp($result,"OK") != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        }
        return $this->response(array('msg'=>'Cobertura creada satisfactoriamente'), RC::HTTP_OK);
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
            return $this->response(array('error'=>'No hay Información para mostrar'), RC::HTTP_FORBIDDEN);

    }

    public function getCoverage_get(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta acción'), RC::HTTP_FORBIDDEN);

        $id = $this->get('id');

        if(empty($id)) return $this->response(array('error'=>'Falta el ID de la Cobertura'), RC::HTTP_BAD_REQUEST);

        if($coverage = $this->Coverages->getCoveragesById($id)){
            return $this->response($coverage, RC::HTTP_OK);
        }else{
            return $this->response(array('error'=>'Número de cobertura no encontrado'), RC::HTTP_FORBIDDEN);

        }
    }

    public function removeCoverage_delete(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta acción'), RC::HTTP_FORBIDDEN);

        $id = $this->get('id');
        
        if(!$this->Coverages->delete($id, $this->token_valid->user_id)){
            return $this->response(array('msg'=>'Error al intentar eliminar Cobertura'), RC::HTTP_OK);
        }else{
            return $this->response(array('msg'=>'Cobertura eliminada satisfactoriamente'), RC::HTTP_OK);
        }
    }
        
    public function updateCoverage_put(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMcoverages",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta acción'), RC::HTTP_FORBIDDEN);


        $post = json_decode(file_get_contents('php://input'));
        $unit_default = ["A", "B", "E", "G", "P", "Q", "R", "V"];
        $id = $this->get('id');

        $plan_id                    = $post->plan_id;
        $medical_insurance_id       = $post->medical_insurance_id;
        $id_units_coverage          = $post->id_units_coverage;
        $data                       = $post->data;

        if(empty($plan_id))                     return $this->response(array('error'=>'No se ha ingresado el id del Plan'), RC::HTTP_BAD_REQUEST);
        if(empty($medical_insurance_id))        return $this->response(array('error'=>'No se ha ingresado el id de la Obra Social'), RC::HTTP_BAD_REQUEST);
        if(empty($id_units_coverage))           return $this->response(array('error'=>'Error en la data enviada'), RC::HTTP_BAD_REQUEST);

        // Range can be 0-100, 2 decimal
        foreach ($data as $h_g){
            $validate_honorary      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $h_g->honorary);
            $validate_expenses      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $h_g->expense);
            if(!$validate_honorary || !$validate_expenses){
                return $this->response(array('error'=>'Error, verifique el valor de Honorarios y/o Gastos'), RC::HTTP_BAD_REQUEST);
            }
        }

        //Validate if exist all units
        foreach ($data as $units){
            $type_units = $units->type_units;
            if($type_units == "Ambulatorio"){ $unit_ambulatorio[] =  $units->units;}
            if($type_units == "Internacion"){ $unit_internacion[] =  $units->units;}
        }

        $units_default_ambulatorio = array_values(array_diff($unit_default, $unit_ambulatorio));
        $units_default_internacion = array_values(array_diff($unit_default, $unit_internacion));

        foreach ($units_default_ambulatorio as $u){
            $default_data = [
                "type_units"=> "Ambulatorio",
                "units"=> $u,
                "honorary"=> 100.00,
                "expense"=>  100.00
            ];
            array_push($data, (object)$default_data);
        }
        foreach ($units_default_internacion as $u){
            $default_data = [
                "type_units"=> "Internacion",
                "units"=> $u,
                "honorary"=> 100.00,
                "expense"=>  100.00
            ];
            array_push($data, (object)$default_data);
        }

        //Update data
        $result = $this->Coverages->update($id, $plan_id,  $medical_insurance_id, $id_units_coverage, $data);
        if($result != 1 ){
            if(strcmp($result,"OK") != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        }
        return $this->response(array('msg'=>'Cobertura actualizada satisfactoriamente'), RC::HTTP_OK);
    }
    
}