<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class FeeController extends AuthController{

    private $token_valid;
    protected $access = "ABMaranceles";

    function __construct(){
        parent::__construct();
        $this->load->model('fee');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create fee
    public function fees_post(){

      $post = json_decode(file_get_contents('php://input'));

      $medical_insurance_id     = $post->medical_insurance_id   ?? "";
      $plan_id                  = $post->plan_id                ?? "";
      $fee_type_id              = $post->fee_type_id            ?? "";
      $upload_date              = $post->upload_date            ?? "";
      $period_since             = $post->period                 ?? "";

      //Fee's units
      $units                  = $post->units                    ?? "";

      //Validate if any obligatory field is missing
      if(empty($medical_insurance_id))                    return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($plan_id))                                 return $this->response(['error'=>'No se ha ingresado plan'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($fee_type_id))                             return $this->response(['error'=>'No se ha ingresado tipo de arancel'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($upload_date))                             return $this->response(['error'=>'No se ha ingresado fecha de alta'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($period_since))                            return $this->response(['error'=>'No se ha ingresado período'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($units) || count($units) <> 8)             return $this->response(['error'=>'No se han ingresado datos para todas las unidades'], REST_Controller::HTTP_BAD_REQUEST);

      //Used to check the number of honoraries send.
      $honoraryQuantity = 0;

      foreach ($units as $unit) {
          if(empty($unit->unit) || strlen($unit->unit) <> 1)                                return $this->response(['error'=>'No se ha ingresado alguna unidad o se ha ingresado un valor incorrecto para la misma'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unit->movement))                                                        return $this->response(['error'=>'No se ha ingresado movimiento para la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
          if((empty($unit->expenses) && $unit->expenses !== '0') || $unit->expenses < 0)    return $this->response(['error'=>'No se ha ingresado un valor valido para gastos para la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unit->honoraries))                                                      return $this->response(['error'=>'No se han ingresado honorarios para la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);

          foreach ($unit->honoraries as $honorary) {
              if(empty($honorary->movement))                                                               return $this->response(['error'=>'No se ha ingresado movimiento para un honorario de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
              if((empty($honorary->value) && $honorary->value !== '0') || $honorary->value < 0)            return $this->response(['error'=>'No se ha ingresado un valor valido para un honorario de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
              if(empty($honorary->id_medical_career) && empty($honorary->id_category_femeba))              return $this->response(['error'=>'No se ha ingresado item para un honorario de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
              $honoraryQuantity ++;
          }
      }

      //Validations
      if(!$this->validator->validateDate($period_since))                        return $this->response(array('error'=>'Fecha del periodo invalida'), REST_Controller::HTTP_BAD_REQUEST);
      if(!$this->validator->validateDate($upload_date))                         return $this->response(array('error'=>'Fecha de carga invalida'), REST_Controller::HTTP_BAD_REQUEST);
      if(!$this->fee->validateHonoraryQuantity($fee_type_id,$honoraryQuantity)) return $this->response(array('error'=>'No se han cargado todos los datos de honorarios para todas las unidades'), REST_Controller::HTTP_BAD_REQUEST);

      //Validate the informed period is previous than the actual period (actual month)
      if (date($period_since) > date("Y-m-d")) return $this->response(array('error'=>'El período no puede ser posterior al periodo actual (Año/Mes actual)'), REST_Controller::HTTP_BAD_REQUEST);

      //Validate fields and unique key
      $error = $this->fee->validateData($medical_insurance_id, $plan_id, $fee_type_id, $period_since);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, save the fee
      $result = $this->fee->save($medical_insurance_id, $plan_id, $fee_type_id, $upload_date, $period_since, $units);
      if($result['status'] == "ok"){
        return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show fees
    public function fees_get(){

        $id = $this->get('id');

        if (!isset($id)){
            $fees = $this->fee->getFees();
            return $this->response($fees, REST_Controller::HTTP_OK);
        }else {
            $fee = $this->fee->getFeeById($id);
            if(empty($fee)){
                return $this->response(['error'=>'No se encontró el ID del arancel'], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                return $this->response($fee, REST_Controller::HTTP_OK);
            }
        }
    }

    //Update fee information
    public function fees_put(){

        $post = json_decode(file_get_contents('php://input'));

        $id = (int) $this->get('id');

        if (empty($id)) return $this->response(['error'=>'No se ha informado el ID del arancel a modificar'], REST_Controller::HTTP_BAD_REQUEST);

        //Fee's units
        $units = $post->units ?? "";

        //Validate if any obligatory field is missing
        if(empty($units) || count($units) <> 8)         return $this->response(['error'=>'No se han ingresado datos para todas las unidades'], REST_Controller::HTTP_BAD_REQUEST);

        foreach ($units as $unit) {
            if(empty($unit->unit) || strlen($unit->unit) <> 1)    return $this->response(['error'=>'No se ha ingresado alguna unidad'], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unit->unit_id))                             return $this->response(['error'=>'No se ha ingresado el ID de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unit->movement))                            return $this->response(['error'=>'No se ha ingresado movimiento para la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
            if((empty($unit->expenses) && $unit->expenses !== '0') || $unit->expenses < 0)    return $this->response(['error'=>'No se ha ingresado un valor valido para gastos para la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unit->honoraries))                          return $this->response(['error'=>'No se han ingresado honorarios para la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);

            foreach ($unit->honoraries as $honorary) {
                if(empty($honorary->movement))                                                           return $this->response(['error'=>'No se ha ingresado movimiento para un honorario de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
                if((empty($honorary->value) && $honorary->value !== '0') || $honorary->value < 0)        return $this->response(['error'=>'No se ha ingresado un valor valido para un honorario de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
                if(empty($honorary->id_medical_career) && empty($honorary->id_category_femeba))          return $this->response(['error'=>'No se ha ingresado item para un honorario de la unidad '.$unit->unit], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        //If everything is valid, update the fee
        if($this->fee->update($units, $id, $this->token_valid->user_id)){
            return $this->response(['msg'=>'Arancel modificado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Delete fee
    public function fees_delete(){

        $id = (int) $this->get('id');
        if (empty($id)) return $this->response(['error'=>'No se ha informado el ID del arancel a eliminar'], REST_Controller::HTTP_BAD_REQUEST);
        if($this->fee->delete($id, $this->token_valid->user_id)){
            return $this->response(['msg'=>'Arancel eliminado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Create new fee based on older fee for informed plans
    public function incrementedFees_post(){

        $post = json_decode(file_get_contents('php://input'));

        $selected_fee_id     = $post->selected_fee_id   ?? "";
        $plans               = $post->plans             ?? "";
        $period_since        = $post->period_since      ?? "";
        $increase_value      = $post->increase_value    ?? "";


        //Validate if any obligatory field is missing
        if(empty($selected_fee_id))                       return $this->response(['error'=>'No se ha informado un arancel a incrementar'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($period_since))                          return $this->response(['error'=>'No se ha ingresado el nuevo período de vigencia'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($increase_value) || $increase_value < 0) return $this->response(['error'=>'No se ha ingresado el porcentaje de incremento o ha ingresado 0'], REST_Controller::HTTP_BAD_REQUEST);

        //Validate the informed period is previous than the actual period (actual month)
        if (!$this->validator->validateDate($period_since)) return $this->response(array('error'=>'La fecha del nuevo período es inválida'), REST_Controller::HTTP_BAD_REQUEST);
        if (date($period_since) > date("Y-m-d"))     return $this->response(array('error'=>'El período no puede ser posterior al periodo actual (Año/Mes actual)'), REST_Controller::HTTP_BAD_REQUEST);

        //Obtain the old fee data and add it's plan id to plans array. Now, plans array has every plan to modify
        $oldFee = $this->fee->getFeeById($selected_fee_id);
        if (empty($oldFee)) return $this->response(['error'=>'No se ha encontrado el arancel elegido para incrementar'], REST_Controller::HTTP_BAD_REQUEST);
        $plans [] = $oldFee["plan_id"];

        //Validate this fee is the in-forge fee (you can only increase in-forge fees)
        if($oldFee['period_until'] != null) return $this->response(['error'=>'No se puede incrementar el porcentaje de este arancel ya que no es un arancel vigente'], REST_Controller::HTTP_BAD_REQUEST);

        //Validate that new period_since is valid and bigger than all plan's period_since
        if (!$this->fee->validateNewPeriodForPlans($oldFee["medical_insurance_id"],$plans,$period_since)) return $this->response(array('error'=>'La fecha del nuevo período es anterior o igual al período de alguno de los planes seleccionados'), REST_Controller::HTTP_BAD_REQUEST);


        //For each plan:
        //1) Obtain the in-force fee to close it
        //2) Create a new fee with increased percentage (this will be the new in-force fee)
        foreach (array_unique($plans) as $plan){

            $result = $this->fee->increaseFeePercentage($oldFee["medical_insurance_id"],$plan,date($period_since),$increase_value);
            if($result['status'] == "error"){
                return $this->response(['error'=>$result['message']], REST_Controller::HTTP_BAD_REQUEST);
            }

        }

        return $this->response(['msg'=>'Arancel/es incrementados exitosamente'], REST_Controller::HTTP_OK);


    }
}
