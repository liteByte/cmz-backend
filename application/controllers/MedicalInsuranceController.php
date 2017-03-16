<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class MedicalInsuranceController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('MedicalInsurance');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create medical insurance
    public function medicalInsurance_post(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMobrassociales",$this->token_valid->permissions))
        return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $post = json_decode(file_get_contents('php://input'));

      $denomination             = $post->denomination               ?? "";
      $settlement_name          = $post->settlement_name            ?? "";
      $address                  = $post->address                    ?? "";
      $location                 = $post->location                   ?? "";
      $postal_code              = $post->postal_code                ?? "";
      $website                  = $post->website                    ?? "";
      $cuit                     = $post->cuit                       ?? "";
      $iva_id                   = $post->iva_id                     ?? "";
      $gross_income             = $post->gross_income               ?? "";
      $payment_deadline         = $post->payment_deadline           ?? "";
      $scope_id                 = $post->scope_id                   ?? "";
      $femeba                   = $post->femeba                     ?? "";
      $ret_jub_femeba           = $post->ret_jub_femeba             ?? "";
      $federation_funds         = $post->federation_funds           ?? "";
      $admin_rights             = $post->admin_rights               ?? "";
      $ret_socios_honorarios    = $post->ret_socios_honorarios      ?? "";
      $ret_socios_gastos        = $post->ret_socios_gastos          ?? "";
      $ret_nosocios_honorarios  = $post->ret_nosocios_honorarios    ?? "";
      $ret_nosocios_gastos      = $post->ret_nosocios_gastos        ?? "";
      $ret_adherente_honorarios = $post->ret_adherente_honorarios   ?? "";
      $ret_adherente_gastos     = $post->ret_adherente_gastos       ?? "";
      $cobertura_fer_noct       = $post->cobertura_fer_noct         ?? "";

      if(empty($denomination))              return $this->response(['error'=>'No se ha ingresado denominación'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($settlement_name))           return $this->response(['error'=>'No se ha ingresado nombre de liquidación'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($address))                   return $this->response(['error'=>'No se ha ingresado dirección'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($location))                  return $this->response(['error'=>'No se ha ingresado localidad'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($postal_code))               return $this->response(['error'=>'No se ha ingresado código postal'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($cuit))                      return $this->response(['error'=>'No se ha ingresado cuit'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($iva_id))                    return $this->response(['error'=>'No se ha ingresado IVA'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($gross_income))              return $this->response(['error'=>'No se ha ingresado ingresos brutos'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($payment_deadline))          return $this->response(['error'=>'No se ha ingresado plazo de pago'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($scope_id))                  return $this->response(['error'=>'Se ha ingresado el alcance incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if(strlen($femeba) <> 1)              return $this->response(['error'=>'Se ha informado FEMEBA incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_socios_honorarios))     return $this->response(['error'=>'No se ha ingresado retención de honorarios de socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_socios_gastos))         return $this->response(['error'=>'No se ha ingresado retención de gastos de socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_nosocios_honorarios))   return $this->response(['error'=>'No se ha ingresado retención de honorarios de no-socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_nosocios_gastos))       return $this->response(['error'=>'No se ha ingresado retención de gastos de no-socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_adherente_honorarios))  return $this->response(['error'=>'No se ha ingresado retención de honorarios de adherentes'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_adherente_gastos))      return $this->response(['error'=>'No se ha ingresado retención de gastos de adherentes'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($cobertura_fer_noct))        return $this->response(['error'=>'No se ha ingresado cobertura nocturna/feriados'], REST_Controller::HTTP_BAD_REQUEST);

      //If femeba is true, check for other necessaryparameters
      if($femeba == 1){
          if(strlen($ret_jub_femeba) <> 1)  return $this->response(['error'=>'Se ha ingresado retención de jubilación de FEMEBA incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($federation_funds))      return $this->response(['error'=>'No se han ingresado fondos de federación'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($admin_rights))          return $this->response(['error'=>'No se han ingresado derechos de admin'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //Validations
      if($payment_deadline <= 0)                                            return $this->response(['error'=>'El plazo de pago debe ser mayor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_socios_honorarios < 0    || $ret_socios_honorarios > 100)     return $this->response(['error'=>'Porcentaje de retención de honorarios de socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_socios_gastos < 0        || $ret_socios_gastos > 100)         return $this->response(['error'=>'Porcentaje de retención de gastos de socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_nosocios_honorarios < 0  || $ret_nosocios_honorarios > 100)   return $this->response(['error'=>'Porcentaje de retención de honorarios de no socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_nosocios_gastos < 0      || $ret_nosocios_gastos > 100)       return $this->response(['error'=>'Porcentaje de retención de gastos de no socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_adherente_honorarios < 0 || $ret_adherente_honorarios > 100)  return $this->response(['error'=>'Porcentaje de retención de honorarios de adherentes ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_adherente_gastos < 0     || $ret_adherente_gastos > 100)      return $this->response(['error'=>'Porcentaje de retención de gastos de adherentes ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($cobertura_fer_noct < 0       || $cobertura_fer_noct > 100)        return $this->response(['error'=>'Porcentaje de cobertura nocturna/feriados ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($femeba == 1){
          if($federation_funds < 0     || $federation_funds > 100)          return $this->response(['error'=>'Porcentaje de fondos de federación ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
          if($admin_rights < 0         || $admin_rights > 100)              return $this->response(['error'=>'Porcentaje de derechos de admin ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //Valid repeated cuit
      $error = $this->MedicalInsurance->validateData($cuit);

      if(strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, save the insurance
      if($this->MedicalInsurance->save($denomination,$settlement_name,$address,$location,$postal_code,$website,$cuit,$iva_id,$gross_income,$payment_deadline,$scope_id,$femeba,$ret_jub_femeba,$federation_funds,$admin_rights,$ret_socios_honorarios,$ret_socios_gastos,$ret_nosocios_honorarios,$ret_nosocios_gastos,$ret_adherente_honorarios,$ret_adherente_gastos,$cobertura_fer_noct)){
        return $this->response(['msg'=>'Obra social creada satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show medical insurances
    public function medicalInsurance_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMobrassociales",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $medicalInsurances = $this->MedicalInsurance->getMedicalInsurances();
      return $this->response($medicalInsurances, REST_Controller::HTTP_OK);
    }

    //Update medical insurance information
    public function updateInsurance_put(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMobrassociales",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $post = json_decode(file_get_contents('php://input'));

      $denomination             = $post->denomination               ?? "";
      $settlement_name          = $post->settlement_name            ?? "";
      $address                  = $post->address                    ?? "";
      $location                 = $post->location                   ?? "";
      $postal_code              = $post->postal_code                ?? "";
      $website                  = $post->website                    ?? "";
      $cuit                     = $post->cuit                       ?? "";
      $iva_id                   = $post->iva_id                     ?? "";
      $gross_income             = $post->gross_income               ?? "";
      $payment_deadline         = $post->payment_deadline           ?? "";
      $scope_id                 = $post->scope_id                   ?? "";
      $femeba                   = $post->femeba                     ?? "";
      $ret_jub_femeba           = $post->ret_jub_femeba             ?? "";
      $federation_funds         = $post->federation_funds           ?? "";
      $admin_rights             = $post->admin_rights               ?? "";
      $ret_socios_honorarios    = $post->ret_socios_honorarios      ?? "";
      $ret_socios_gastos        = $post->ret_socios_gastos          ?? "";
      $ret_nosocios_honorarios  = $post->ret_nosocios_honorarios    ?? "";
      $ret_nosocios_gastos      = $post->ret_nosocios_gastos        ?? "";
      $ret_adherente_honorarios = $post->ret_adherente_honorarios   ?? "";
      $ret_adherente_gastos     = $post->ret_adherente_gastos       ?? "";
      $cobertura_fer_noct       = $post->cobertura_fer_noct         ?? "";
      $id                       = (int) $this->get('id');

      if(empty($denomination))              return $this->response(['error'=>'No se ha ingresado denominación'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($settlement_name))           return $this->response(['error'=>'No se ha ingresado nombre de liquidación'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($address))                   return $this->response(['error'=>'No se ha ingresado dirección'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($location))                  return $this->response(['error'=>'No se ha ingresado localidad'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($postal_code))               return $this->response(['error'=>'No se ha ingresado código postal'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($cuit))                      return $this->response(['error'=>'No se ha ingresado cuit'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($iva_id))                    return $this->response(['error'=>'No se ha ingresado IVA'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($gross_income))              return $this->response(['error'=>'No se ha ingresado ingresos brutos'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($payment_deadline))          return $this->response(['error'=>'No se ha ingresado plazo de pago'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($scope_id))                  return $this->response(['error'=>'Se ha ingresado el alcance incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if(strlen($femeba) <> 1)              return $this->response(['error'=>'Se ha informado FEMEBA incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_socios_honorarios))     return $this->response(['error'=>'No se ha ingresado retención de honorarios de socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_socios_gastos))         return $this->response(['error'=>'No se ha ingresado retención de gastos de socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_nosocios_honorarios))   return $this->response(['error'=>'No se ha ingresado retención de honorarios de no-socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_nosocios_gastos))       return $this->response(['error'=>'No se ha ingresado retención de gastos de no-socios'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_adherente_honorarios))  return $this->response(['error'=>'No se ha ingresado retención de honorarios de adherentes'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($ret_adherente_gastos))      return $this->response(['error'=>'No se ha ingresado retención de gastos de adherentes'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($cobertura_fer_noct))        return $this->response(['error'=>'No se ha ingresado cobertura nocturna/feriados'], REST_Controller::HTTP_BAD_REQUEST);

      //If femeba is true, check for other necessaryparameters
      if($femeba == 1){
          if(strlen($ret_jub_femeba) <> 1)  return $this->response(['error'=>'Se ha ingresado retención de jubilación de FEMEBA incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($federation_funds))      return $this->response(['error'=>'No se han ingresado fondos de federación'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($admin_rights))          return $this->response(['error'=>'No se han ingresado derechos de admin'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //Validations
      if($payment_deadline <= 0)                                            return $this->response(['error'=>'El plazo de pago debe ser mayor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_socios_honorarios < 0    || $ret_socios_honorarios > 100)     return $this->response(['error'=>'Porcentaje de retención de honorarios de socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_socios_gastos < 0        || $ret_socios_gastos > 100)         return $this->response(['error'=>'Porcentaje de retención de gastos de socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_nosocios_honorarios < 0  || $ret_nosocios_honorarios > 100)   return $this->response(['error'=>'Porcentaje de retención de honorarios de no socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_nosocios_gastos < 0      || $ret_nosocios_gastos > 100)       return $this->response(['error'=>'Porcentaje de retención de gastos de no socios ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_adherente_honorarios < 0 || $ret_adherente_honorarios > 100)  return $this->response(['error'=>'Porcentaje de retención de honorarios de adherentes ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($ret_adherente_gastos < 0     || $ret_adherente_gastos > 100)      return $this->response(['error'=>'Porcentaje de retención de gastos de adherentes ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($cobertura_fer_noct < 0       || $cobertura_fer_noct > 100)        return $this->response(['error'=>'Porcentaje de cobertura nocturna/feriados ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      if($femeba == 1){
          if($federation_funds < 0     || $federation_funds > 100)          return $this->response(['error'=>'Porcentaje de fondos de federación ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
          if($admin_rights < 0         || $admin_rights > 100)              return $this->response(['error'=>'Porcentaje de derechos de admin ingresados incorrectamente'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //Valid repeated cuit
      $error = $this->MedicalInsurance->validateDataOnUpdate($cuit,$id);

      if(strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, update the insurance
      if($this->MedicalInsurance->update($denomination,$settlement_name,$address,$location,$postal_code,$website,$cuit,$iva_id,$gross_income,$payment_deadline,$scope_id,$femeba,$ret_jub_femeba,$federation_funds,$admin_rights,$ret_socios_honorarios,$ret_socios_gastos,$ret_nosocios_honorarios,$ret_nosocios_gastos,$ret_adherente_honorarios,$ret_adherente_gastos,$cobertura_fer_noct,$id,$this->token_valid->user_id)){
        return $this->response(['msg'=>'Obra social modificada satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show specific medical insurance
    public function getInsurance_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMobrassociales",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = $this->get('id');

      if(empty($id)) return $this->response(['error'=>'Falta el ID de la obra social'], REST_Controller::HTTP_BAD_REQUEST);

      $medicalInsurance = $this->MedicalInsurance->getInsuranceById($id);

      if(empty($medicalInsurance)){
        return $this->response(['error'=>'No se encontro el ID de obra social'], REST_Controller::HTTP_BAD_REQUEST);
      } else {
        return $this->response($medicalInsurance, REST_Controller::HTTP_OK);
      }
    }

    //Delete medical insurance
    public function removeInsurance_delete(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMobrassociales",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = (int) $this->get('id');

      if($this->MedicalInsurance->delete($id,$this->token_valid->user_id)){
        return $this->response(['msg'=>'Obra social eliminada satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
