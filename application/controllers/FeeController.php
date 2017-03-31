<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class FeeController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('fee');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create fee
    public function fees_post(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMaranceles",$this->token_valid->permissions))
        return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $post = json_decode(file_get_contents('php://input'));

      $medical_insurance_id     = $post->medical_insurance_id   ?? "";
      $plan_id                  = $post->plan_id                ?? "";
      $fee_type_id              = $post->fee_type_id            ?? "";
      $upload_date              = $post->upload_date            ?? "";
      $period                   = $post->period                 ?? "";

      //Fee's unities
      $unities                  = $post->unities                ?? "";

      //Validate if any obligatory field is missing
      if(empty($medical_insurance_id))                    return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($plan_id))                                 return $this->response(['error'=>'No se ha ingresado plan'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($fee_type_id))                             return $this->response(['error'=>'No se ha ingresado tipo de arancel'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($upload_date))                             return $this->response(['error'=>'No se ha ingresado fecha de alta'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($period))                                  return $this->response(['error'=>'No se ha ingresado período'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($unities) || count($unities) <> 8)         return $this->response(['error'=>'No se han ingresado datos para todas las unidades'], REST_Controller::HTTP_BAD_REQUEST);

      foreach ($unities as $unity) {
          if(empty($unity->unity) || strlen($unity->unity) <> 1)    return $this->response(['error'=>'No se ha ingresado alguna unidad o se ha ingresado un valor incorrecto para la misma'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unity->movement))                               return $this->response(['error'=>'No se ha ingresado movimiento para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unity->expenses) || $unity->expenses < 0)       return $this->response(['error'=>'No se ha ingresado un valor valido para gastos para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unity->honoraries))                             return $this->response(['error'=>'No se han ingresado honorarios para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);

          foreach ($unity->honoraries as $honorary) {
              if(empty($honorary->movement))                                                               return $this->response(['error'=>'No se ha ingresado movimiento para un honorario de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
              if(empty($honorary->value) || $honorary->value < 0)                                          return $this->response(['error'=>'No se ha ingresado un valor valido para un honorario de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
              if(empty($honorary->id_medical_career) && empty($honorary->id_category_femeba))              return $this->response(['error'=>'No se ha ingresado item para un honorario de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
          }
      }

      //Validations
      if(!$this->validator->validateDate($period))          return $this->response(array('error'=>'Fecha del periodo invalida'), REST_Controller::HTTP_BAD_REQUEST);
      if(!$this->validator->validateDate($upload_date))     return $this->response(array('error'=>'Fecha de carga invalida'), REST_Controller::HTTP_BAD_REQUEST);

      //Validate fields and unique key
      $error = $this->fee->validateData($medical_insurance_id, $plan_id, $fee_type_id, $period);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, save the fee
      if($this->fee->save($medical_insurance_id, $plan_id, $fee_type_id, $upload_date, $period, $unities)){
        return $this->response(['msg'=>'Arancel creado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show fees
    public function fees_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMaranceles",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = $this->get('id');

      if (!isset($id)){

          $fees = $this->fee->getFees();
          return $this->response($fees, REST_Controller::HTTP_OK);

      } else {

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

        //Validates if the user is logged and the token sent is valid.
        if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

        //Validates if the user has permissions to do this action
        if(!in_array("ABMaranceles",$this->token_valid->permissions))
          return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

        $post = json_decode(file_get_contents('php://input'));

        $id = (int) $this->get('id');

        //Fee's unities
        $unities = $post->unities ?? "";

        //Validate if any obligatory field is missing
        if(empty($unities) || count($unities) <> 8)         return $this->response(['error'=>'No se han ingresado datos para todas las unidades'], REST_Controller::HTTP_BAD_REQUEST);

        foreach ($unities as $unity) {
            if(empty($unity->unity) || strlen($unity->unity) <> 1)  return $this->response(['error'=>'No se ha ingresado alguna unidad'], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unity->unity_id))                             return $this->response(['error'=>'No se ha ingresado el ID de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unity->movement))                             return $this->response(['error'=>'No se ha ingresado movimiento para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unity->expenses) || $unity->expenses < 0)     return $this->response(['error'=>'No se ha ingresado un valor valido para gastos para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($unity->honoraries))                           return $this->response(['error'=>'No se han ingresado honorarios para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);

            foreach ($unity->honoraries as $honorary) {
                if(empty($honorary->movement))                                                           return $this->response(['error'=>'No se ha ingresado movimiento para un honorario de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
                if(empty($honorary->value) || $honorary->value < 0)                                      return $this->response(['error'=>'No se ha ingresado un valor valido para un honorario de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
                if(empty($honorary->id_medical_career) && empty($honorary->id_category_femeba))          return $this->response(['error'=>'No se ha ingresado item para un honorario de la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        //If everything is valid, update the fee
        if($this->fee->update($unities, $id, $this->token_valid->user_id)){
          return $this->response(['msg'=>'Arancel modificado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
          return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Delete fee
    public function fees_delete(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMaranceles",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = (int) $this->get('id');

      if (empty($id)) return $this->response(['error'=>'No se ha informado el ID del arancel a eliminar'], REST_Controller::HTTP_BAD_REQUEST);

      if($this->fee->delete($id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Arancel eliminado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
