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


      if(empty($medical_insurance_id))                    return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($plan_id))                                 return $this->response(['error'=>'No se ha ingresado plan'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($fee_type_id))                             return $this->response(['error'=>'No se ha ingresado tipo de arancel'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($upload_date))                             return $this->response(['error'=>'No se ha ingresado fecha de alta'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($period))                                  return $this->response(['error'=>'No se ha ingresado periodo'], REST_Controller::HTTP_BAD_REQUEST);

      foreach ($unities as $unity) {
          if(empty($unity->unity))                        return $this->response(['error'=>'No se ha ingresado alguna unidad'], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unity->movement))                     return $this->response(['error'=>'No se ha ingresado movimiento para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unity->expenses))                     return $this->response(['error'=>'No se ha ingresado gastos para la unidad '.$unity->unity], REST_Controller::HTTP_BAD_REQUEST);
          if(empty($unity->honoraries))                   return $this->response(['error'=>'No se han ingresado honorarios para la unidad '.], REST_Controller::HTTP_BAD_REQUEST);
      }


      //Validations
      $unities = ['P','Q','R','G','B','V','A','E'];
      if (!in_array($unity, $unities))      return $this->response(['error'=>'La unidad indicada no es válida'], REST_Controller::HTTP_BAD_REQUEST);
      if($speciality_unity < 0 )            return $this->response(['error'=>'La unidad de especialidad no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($help_unity < 0 )                  return $this->response(['error'=>'La unidad de ayuda no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($anesthetist_unity < 0 )           return $this->response(['error'=>'La unidad de anestesista no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($spending_unity < 0 )              return $this->response(['error'=>'La unidad de gastos no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if(!empty($helpers)){
          if($helpers < 0 || $helpers > 9)  return $this->response(['error'=>'La unidad de gastos no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //Validate repeated code+class combination
      $error = $this->fee->validateData($code, $class);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, save the contact
      if($this->fee->save($type, $code, $class, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity)){
        return $this->response(['msg'=>'Nomenclador creado satisfactoriamente'], REST_Controller::HTTP_OK);
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

      //Validate id
      if (empty($id)) return $this->response(['error'=>'Falta el ID del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);

      if (!isset($id)){

          $fees = $this->fee->getfees();
          return $this->response($fees, REST_Controller::HTTP_OK);

      } else {

          $fee = $this->fee->getfeeById($id);

          if(empty($fee)){
            return $this->response(['error'=>'No se encontró el ID del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);
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

      $type               = $post->type                ?? "NN";
      $description        = $post->description         ?? "";
      $unity              = $post->unity               ?? "";
      $speciality_unity   = $post->speciality_unity    ?? 0;
      $helpers            = $post->helpers             ?? 0;
      $help_unity         = $post->help_unity          ?? 0;
      $anesthetist_unity  = $post->anesthetist_unity   ?? 0;
      $spending_unity     = $post->spending_unity      ?? 0;
      $id                 = (int) $this->get('id');

      if(empty($type))                                                  return $this->response(['error'=>'No se ha ingresado tipo'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($description))                                           return $this->response(['error'=>'No se ha ingresado descripción'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($unity))                                                 return $this->response(['error'=>'No se ha ingresado unidad'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($speciality_unity)   && strlen($speciality_unity) == 0)  return $this->response(['error'=>'No se ha ingresado unidad de especialidad'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($help_unity)         && strlen($help_unity) == 0)        return $this->response(['error'=>'No se ha ingresado unidad de ayuda'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($anesthetist_unity)  && strlen($anesthetist_unity) == 0) return $this->response(['error'=>'No se ha ingresado unidad de anestesista'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($spending_unity)     && strlen($spending_unity) == 0)    return $this->response(['error'=>'No se ha ingresado unidad de gasto'], REST_Controller::HTTP_BAD_REQUEST);

      //Validations
      $unities = ['P','Q','R','G','B','V','A','E'];
      if (!in_array($unity, $unities))      return $this->response(['error'=>'La unidad indicada no es válida'], REST_Controller::HTTP_BAD_REQUEST);
      if($speciality_unity < 0 )            return $this->response(['error'=>'La unidad de especialidad no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($help_unity < 0 )                  return $this->response(['error'=>'La unidad de ayuda no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($anesthetist_unity < 0 )           return $this->response(['error'=>'La unidad de anestesista no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if($spending_unity < 0 )              return $this->response(['error'=>'La unidad de gastos no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      if(!empty($helpers)){
          if($helpers < 0 || $helpers > 9)  return $this->response(['error'=>'La unidad de gastos no puede ser menor a 0 ni mayor a 9'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //If everything is valid, update the contact
      if($this->fee->update($type, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity, $id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Nomenclador modificado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show specific fee
    /*public function fees_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMaranceles", $this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = $this->get('id');

      if(empty($id)) return $this->response(['error'=>'Falta el ID del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);

      $fee = $this->fee->getfeeById($id);

      if(empty($fee)){
        return $this->response(['error'=>'No se encontró el ID del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);
      } else {
        return $this->response($fee, REST_Controller::HTTP_OK);
      }
  }*/

    //Delete fee
    public function fees_delete(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMaranceles",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = (int) $this->get('id');

      if($this->fee->delete($id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Nomenclador eliminado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
