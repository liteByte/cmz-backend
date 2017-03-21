<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ContactController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('Contact');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create contact
    public function contacts_post(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMcontactos",$this->token_valid->permissions))
        return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $post = json_decode(file_get_contents('php://input'));

      $denomination     = $post->denomination   ?? "";
      $sector           = $post->sector         ?? "";
      $phone_number     = $post->phone_number   ?? "";
      $email            = $post->email          ?? "";

      if(empty($denomination))      return $this->response(['error'=>'No se ha ingresado denominacion'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($sector))            return $this->response(['error'=>'No se ha ingresado sector/referente'], REST_Controller::HTTP_BAD_REQUEST);

      //Email validation
      if(!empty($email)){
          if(!valid_email($email))  return $this->response(['error'=>'El formato de email no es correcto'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //If everything is valid, save the contact
      if($this->Contact->save($denomination, $sector, $phone_number, $email)){
        return $this->response(['msg'=>'Contacto creado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show contacts
    public function contacts_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMcontactos",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $contacts = $this->Contact->getContacts();
      return $this->response($contacts, REST_Controller::HTTP_OK);
    }

    //Update contact information
    public function updateContact_put(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMcontactos",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $post = json_decode(file_get_contents('php://input'));

      $denomination     = $post->denomination   ?? "";
      $sector           = $post->sector         ?? "";
      $phone_number     = $post->phone_number   ?? "";
      $email            = $post->email          ?? "";
      $id               = (int) $this->get('id');

      if(empty($denomination))      return $this->response(['error'=>'No se ha ingresado denominacion'], REST_Controller::HTTP_BAD_REQUEST);
      if(empty($sector))            return $this->response(['error'=>'No se ha ingresado sector/referente'], REST_Controller::HTTP_BAD_REQUEST);

      //Email validation
      if(!empty($email)){
          if(!valid_email($email))  return $this->response(['error'=>'El formato de email no es correcto'], REST_Controller::HTTP_BAD_REQUEST);
      }

      //If everything is valid, update the contact
      if($this->Contact->update($denomination, $sector, $phone_number, $email, $id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Contacto modificado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

    //Show specific contact
    public function getContact_get(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMcontactos", $this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = $this->get('id');

      if(empty($id)) return $this->response(['error'=>'Falta el ID del contacto'], REST_Controller::HTTP_BAD_REQUEST);

      $contact = $this->Contact->getContactById($id);

      if(empty($contact)){
        return $this->response(['error'=>'No se encontro el ID del contacto'], REST_Controller::HTTP_BAD_REQUEST);
      } else {
        return $this->response($contact, REST_Controller::HTTP_OK);
      }
    }

    //Delete contact
    public function removeContact_delete(){

      //Validates if the user is logged and the token sent is valid.
      if($this->token_valid->status != "ok") return $this->response(['error'=>$this->token_valid->message], REST_Controller::HTTP_UNAUTHORIZED);

      //Validates if the user has permissions to do this action
      if(!in_array("ABMcontactos",$this->token_valid->permissions))
         return $this->response(['error'=>'No tiene los permisos para realizar esta acción'], REST_Controller::HTTP_FORBIDDEN);

      $id = (int) $this->get('id');

      if($this->Contact->delete($id, $this->token_valid->user_id)){
        return $this->response(['msg'=>'Contacto eliminado satisfactoriamente'], REST_Controller::HTTP_OK);
      } else {
        return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
