<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class NomenclatorController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('Nomenclator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create nomenclator
    public function nomenclators_post(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMnomenclador",$this->token_valid->permissions))
        return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $post = json_decode(file_get_contents('php://input'));

      $type               = $post->type                ?? "NN";
      $code               = $post->code                ?? "";
      $class              = $post->class               ?? "";
      $description        = $post->description         ?? "";
      $unity              = $post->unity               ?? "";
      $speciality_unity   = $post->speciality_unity    ?? 0;
      $helpers            = $post->helpers             ?? 0;
      $help_unity         = $post->help_unity          ?? 0;
      $anesthetist_unity  = $post->anesthetist_unity   ?? 0;
      $spending_unity     = $post->spending_unity      ?? 0;

      if(empty($type))                                                  return $this->response(['error'=>'No se ha ingresado tipo'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($code))                                                  return $this->response(['error'=>'No se ha ingresado código'], REST_Controller::HTTP_BAD_REQUEST);
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
          if($helpers < 0 || $helpers > 9)  return $this->response(['error'=>'La unidad de gastos no puede ser menor a 0'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //Validate repeated code+class combination
      $error = $this->Nomenclator->validateData($code, $class);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, save the contact
      if($this->Nomenclator->save($type, $code, $class, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity)){
        return $this->response(['msg'=>'Nomenclador creado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show nomenclators
    public function nomenclators_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMnomenclador",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $nomenclators = $this->Nomenclator->getNomenclators();
      return $this->response($nomenclators, REST_Controller::HTTP_OK);
    }

    //Update nomenclator information
    public function updateNomenclator_put(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMnomenclador",$this->token_valid->permissions))
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
      if($this->Nomenclator->update($type, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity, $id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Nomenclador modificado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show specific nomenclator
    public function getNomenclator_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMnomenclador", $this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = $this->get('id');

      if(empty($id)) return $this->response(['error'=>'Falta el ID del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);

      $nomenclator = $this->Nomenclator->getNomenclatorById($id);

      if(empty($nomenclator)){
        return $this->response(['error'=>'No se encontró el ID del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);
      } else {
        return $this->response($nomenclator, REST_Controller::HTTP_OK);
      }
    }

    //Delete nomenclator
    public function removeNomenclator_delete(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMnomenclador",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = (int) $this->get('id');

      if($this->Nomenclator->delete($id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Nomenclador eliminado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}