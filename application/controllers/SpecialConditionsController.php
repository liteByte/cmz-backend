<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class SpecialConditionsController extends AuthController{

    function __construct(){
        parent::__construct();
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function specialconditions_post(){
        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), RC::HTTP_BAD_REQUEST);

        if (!in_array("ABMcondicionesespeciales", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta acción'), RC::HTTP_UNAUTHORIZED);

        $post = json_decode(file_get_contents('php://input'));

        $medical_insurance_id   = $post->medical_insurance_id   ?? "";
        $plan_id                = $post->plan_id                ?? "";
        $nomenclator_type       = $post->nomenclator_type       ?? "";
        $provision              = $post->provision              ?? "";
        $type                   = $post->type                   ?? "";
        $period_of_validity     = $post->period_of_validity     ?? "";

        if (empty($medical_insurance_id)) return $this->response(array('error' => 'No se ha ingresado código de la Obra social'), RC::HTTP_BAD_REQUEST);
        if (empty($plan_id))              return $this->response(array('error' => 'No se ha ingresado código del plan'), RC::HTTP_BAD_REQUEST);
        if (empty($nomenclator_type))     return $this->response(array('error' => 'No se ha ingresado el tipo de nomenclador'), RC::HTTP_BAD_REQUEST);
        if (empty($provision))            return $this->response(array('error' => 'No se ha ingresado la  prestación'), RC::HTTP_BAD_REQUEST);
        if (empty($type))                 return $this->response(array('error' => 'No se ha ingresado el tipo de prestación'), RC::HTTP_BAD_REQUEST);
        if (empty($period_of_validity))   return $this->response(array('error' => 'No se ha ingresado el período vigencia'), RC::HTTP_BAD_REQUEST);

        if(!isset($post->type_of_values)) return $this->response(array('error'=>'Se debe indicar el tipo de valor, para realizar el cálculo'), RC::HTTP_BAD_REQUEST);
        $type_of_values = $post->type_of_values                       ?? "";
        $type_of_values = (boolval($type_of_values) ? 'true' : 'false');

        if(!isset($post->group_of_values)) return $this->response(array('error'=>'Se debe indicar el grupo de valores que se aplicara'), RC::HTTP_BAD_REQUEST);
        $group_of_values= $post->group_of_values                       ?? "";
        $group_of_values = (boolval($group_of_values) ? 1 : 0);

        if($group_of_values){
            $especiales = $post->especiales;
            $default = ['Ambulatorio', 'Internación'];
            foreach ($especiales as $esp){
                $check[]= $esp->type_unit;
                $check_result = array_values(array_diff( $default, $check ));

                $honorary  = $esp->honorary   ?? "";
                if (empty($honorary) and   !is_numeric($honorary))  return $this->response(array('error' => 'Se debe ingresar el monto para Honorarios'), RC::HTTP_BAD_REQUEST);

                $expenses  = $esp->expenses   ?? "";
                if (empty($expenses) and   !is_numeric($expenses))  return $this->response(array('error' => 'Se debe ingresar el monto para Gastos'), RC::HTTP_BAD_REQUEST);
            }
            if(!empty($check_result)) return $this->response(array('error'=>'Se debe ingresar el tipo de Unidad '), RC::HTTP_BAD_REQUEST);
            if($type_of_values){
                echo "test";
            }

            die();

        }else{
            echo "unidad";
        }
    }
}

