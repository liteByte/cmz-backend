<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class SpecialConditionsController extends AuthController{

    protected $access = "ABMcondicionesespeciales";
    function __construct(){
        parent::__construct();
        $this->load->model('SpecialConditions');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function specialconditions_post(){

        $post = json_decode(file_get_contents('php://input'));
        $medical_insurance_id   = $post->medical_insurance_id   ?? "";
        $plan_id                = $post->plan_id                ?? "";
        $provision              = $post->provision              ?? "";
        $type                   = $post->type                   ?? "";
        $period_of_validity     = $post->period_of_validity     ?? "";
        $unit                   = $post->unit                   ?? "";
        $quantity_units         = $post->quantity_units         ?? "";

        if (empty($medical_insurance_id)) return $this->response(array('error' => 'No se ha ingresado código de la Obra social'), RC::HTTP_BAD_REQUEST);
        if (empty($plan_id))              return $this->response(array('error' => 'No se ha ingresado código del plan'), RC::HTTP_BAD_REQUEST);
        if (empty($provision))            return $this->response(array('error' => 'No se ha ingresado el Id del nomenclador'), RC::HTTP_BAD_REQUEST);
        if (empty($type))                 return $this->response(array('error' => 'No se ha ingresado el tipo de prestación'), RC::HTTP_BAD_REQUEST);
        if (empty($period_of_validity))   return $this->response(array('error' => 'No se ha ingresado el período vigencia'), RC::HTTP_BAD_REQUEST);

        if(!isset($post->type_of_values)) return $this->response(array('error'=>'Se debe indicar el tipo de valor, para realizar el cálculo'), RC::HTTP_BAD_REQUEST);
        $type_of_values = $post->type_of_values ?? "";
        $type_of_values = (boolval($type_of_values) ? 1 : 0);

        if(!isset($post->group_of_values)) return $this->response(array('error'=>'Se debe indicar el grupo de valores que se aplicara'), RC::HTTP_BAD_REQUEST);
        $group_of_values= $post->group_of_values                       ?? "";
        $group_of_values = (boolval($group_of_values) ? 1 : 0);



        if($group_of_values){
            // Specials Values
            $especiales = $post->especiales;
            $default = ['Ambulatorio', 'Internación'];

            foreach ($especiales as $esp){
                $check[]= $esp->type_unit;
                $check_result = array_values(array_diff( $default, $check ));
                $honorary  = $esp->honorary   ?? "";

                if(empty($honorary) && !is_numeric($honorary))  return $this->response(array('error' => 'Se debe ingresar el monto para Honorarios'), RC::HTTP_BAD_REQUEST);

                $expenses  = $esp->expenses   ?? "";
                if(empty($expenses) && !is_numeric($expenses))  return $this->response(array('error' => 'Se debe ingresar el monto para Gastos'), RC::HTTP_BAD_REQUEST);

                if($type_of_values){
                    if($honorary < 0 || $expenses < 0 ) return $this->response(array('error' => 'Valor de honorarios y/o gastos, invalidos'), RC::HTTP_BAD_REQUEST);
                }else{
                    $validate_honorary      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $esp->honorary);
                    $validate_expenses      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $esp->expenses);
                    if(!$validate_honorary || !$validate_expenses){
                        return $this->response(array('error'=>'Error, verifique el valor de Honorario y/o Gastos'), RC::HTTP_BAD_REQUEST);
                    }
                }
            }

            if(!empty($check_result)) return $this->response(array('error'=>'Se debe ingresar el tipo de Unidad '), RC::HTTP_BAD_REQUEST);

            // Save data
            $result = $this->SpecialConditions->save_special($medical_insurance_id, $plan_id, $provision, $type, $period_of_validity, $type_of_values, $group_of_values, $especiales );
            if(strcmp($result, true ) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);

            if($result){
                return $this->response(array('msg'=>'Condición creada satisfactoriamente'), RC::HTTP_OK);
            }


        }else{
            //Values for Units
            if (empty($unit)) return $this->response(array('error' => 'No se ha ingresado la unidad'), RC::HTTP_BAD_REQUEST);
            if(empty($quantity_units) && !is_numeric($quantity_units))  return $this->response(array('error' => 'Se debe ingresar la ingresado la cantidad de unidades'), RC::HTTP_BAD_REQUEST);
            if($quantity_units < 0 ) return $this->response(array('error' => 'Valor de la unidad, es invalido'), RC::HTTP_BAD_REQUEST);

            $result = $this->SpecialConditions->save_unit($medical_insurance_id, $plan_id, $provision, $type, $period_of_validity, $type_of_values, $group_of_values,$unit, $quantity_units );

            if(strcmp($result, true ) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);

            if($result){
                return $this->response(array('msg'=>'Condición creada satisfactoriamente'), RC::HTTP_OK);
            }
        }
    }

    public function specialconditions_put(){

        $id = $this->get('id');
        $post = json_decode(file_get_contents('php://input'));
        $medical_insurance_id   = $post->medical_insurance_id   ?? "";
        $plan_id                = $post->plan_id                ?? "";
        $provision              = $post->provision              ?? "";
        $type                   = $post->type                   ?? "";
        $period_of_validity     = $post->period_of_validity     ?? "";
        $unit                   = $post->unit                   ?? "";
        $quantity_units         = $post->quantity_units         ?? "";


        if (empty($medical_insurance_id)) return $this->response(array('error' => 'No se ha ingresado código de la Obra social'), RC::HTTP_BAD_REQUEST);
        if (empty($plan_id))              return $this->response(array('error' => 'No se ha ingresado código del plan'), RC::HTTP_BAD_REQUEST);
        if (empty($provision))            return $this->response(array('error' => 'No se ha ingresado la  prestación'), RC::HTTP_BAD_REQUEST);
        if (empty($type))                 return $this->response(array('error' => 'No se ha ingresado el tipo de prestación'), RC::HTTP_BAD_REQUEST);
        if (empty($period_of_validity))   return $this->response(array('error' => 'No se ha ingresado el período vigencia'), RC::HTTP_BAD_REQUEST);

        if(!isset($post->type_of_values)) return $this->response(array('error'=>'Se debe indicar el tipo de valor, para realizar el cálculo'), RC::HTTP_BAD_REQUEST);
        $type_of_values = $post->type_of_values ?? "";
        $type_of_values = (boolval($type_of_values) ? 1 : 0);

        if(!isset($post->group_of_values)) return $this->response(array('error'=>'Se debe indicar el grupo de valores que se aplicara'), RC::HTTP_BAD_REQUEST);
        $group_of_values= $post->group_of_values                       ?? "";
        $group_of_values = (boolval($group_of_values) ? 1 : 0);


        if($group_of_values){
            // Specials Values
            $especiales = $post->especiales;
            $default = ['Ambulatorio', 'Internación'];

            foreach ($especiales as $esp){
                $check[]= $esp->type_unit;
                $check_result = array_values(array_diff( $default, $check ));
                $honorary  = $esp->honorary   ?? "";

                if(empty($honorary) && !is_numeric($honorary))  return $this->response(array('error' => 'Se debe ingresar el monto para Honorarios'), RC::HTTP_BAD_REQUEST);

                $expenses  = $esp->expenses   ?? "";
                if(empty($expenses) && !is_numeric($expenses))  return $this->response(array('error' => 'Se debe ingresar el monto para Gastos'), RC::HTTP_BAD_REQUEST);

                if($type_of_values){
                    if($honorary < 0 || $expenses < 0 ) return $this->response(array('error' => 'Valor de honorarios y/o gastos, invalidos'), RC::HTTP_BAD_REQUEST);
                }else{
                    $validate_honorary      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $esp->honorary);
                    $validate_expenses      = preg_match("/^(?:100|\d{1,2})(?:\.\d{1,2})?$/", $esp->expenses);
                    if(!$validate_honorary || !$validate_expenses){
                        return $this->response(array('error'=>'Error, verifique el valor de Honorario y/o Gastos'), RC::HTTP_BAD_REQUEST);
                    }
                }
            }

            if(!empty($check_result)) return $this->response(array('error'=>'Se debe ingresar el tipo de Unidad '), RC::HTTP_BAD_REQUEST);

            // Update data
            $result = $this->SpecialConditions->update_special($medical_insurance_id, $plan_id, $provision, $type, $period_of_validity, $type_of_values, $group_of_values, $especiales, $id );
            if(strcmp($result, true ) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);

            if($result){
                return $this->response(array('msg'=>'Condición actualizada satisfactoriamente'), RC::HTTP_OK);
            }


        }else{
            //Values for Units
            if (empty($unit)) return $this->response(array('error' => 'No se ha ingresado la unidad'), RC::HTTP_BAD_REQUEST);
            if(empty($quantity_units) && !is_numeric($quantity_units))  return $this->response(array('error' => 'Se debe ingresar la ingresado la cantidad de unidades'), RC::HTTP_BAD_REQUEST);
            if($quantity_units < 0 ) return $this->response(array('error' => 'Valor de la unidad, es invalido'), RC::HTTP_BAD_REQUEST);

            $result = $this->SpecialConditions->update_unit($medical_insurance_id, $plan_id, $provision, $type, $period_of_validity, $type_of_values, $group_of_values,$unit, $quantity_units, $id );

            if(strcmp($result, true ) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);

            if($result){
                return $this->response(array('msg'=>'Condición actualizada satisfactoriamente'), RC::HTTP_OK);
            }
        }



    }

    public function specialconditions_get(){

        $result = $this->SpecialConditions->get_specialconditions();
        if($result){
            return $this->response($result, RC::HTTP_OK);
        }else{
            return $this->response(array('error'=>'No hay Información para mostrar'), RC::HTTP_FORBIDDEN);
        }
    }

    public function specialconditions_by_id_get(){

        $id = $this->get('id');
        if(empty($id)) return $this->response(array('error'=>'Falta el ID de la condición especial'), RC::HTTP_BAD_REQUEST);
        $result = $this->SpecialConditions->get_specialconditions_by_id($id);

        if($result){
            return $this->response($result, RC::HTTP_OK);
        }else{
            return $this->response(array('error'=>'No hay Información para mostrar'), RC::HTTP_FORBIDDEN);
        }
    }

    public function specialconditions_delete(){

        $id = $this->get('id');
        $result = $this->SpecialConditions->delete_specialconditions($id);

        if(strcmp($result, true ) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        if($result){
            return $this->response(array('msg'=>'Condición eliminada satisfactoriamente'), RC::HTTP_OK);
        }
    }
}

