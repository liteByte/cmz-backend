<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class BankController extends AuthController{

		private $token_valid;

		function __construct(){
			parent::__construct();
			$this->load->model('Bank');
			$this->token_valid = $this->validateToken(apache_request_headers());
		}

    //Create bank
    public function banks_post(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

			//Validates if the user has permissions to do this action
			if(!in_array("ABMbancos",$this->token_valid->permissions))
				return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

      $post = json_decode(file_get_contents('php://input'));

      $bank_code        = $post->bank_code;
      $corporate_name   = $post->corporate_name;
      $address          = $post->address;
      $location         = $post->location;
      $phone_number     = $post->phone_number;

      if(empty($bank_code))        return $this->response(array('error'=>'No se ha ingresado codigo de banco'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($corporate_name))   return $this->response(array('error'=>'No se ha ingresado razon social'), REST_Controller::HTTP_BAD_REQUEST);

      //Valid repeated bank code
      $error = $this->Bank->validateData($bank_code);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      //If everything is valid, save the bank
      if($this->Bank->save($bank_code,$corporate_name,$address,$location,$phone_number)){
        return $this->response(array('msg'=>'Banco creado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}

    }

    //Show banks
    public function banks_get(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

			//Validates if the user has permissions to do this action
			if(!in_array("ABMbancos",$this->token_valid->permissions))
				return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

      $banks = $this->Bank->getBanks();
      return $this->response($banks, REST_Controller::HTTP_OK);
    }

    //Update bank information
    public function updateBank_put(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

			//Validates if the user has permissions to do this action
			if(!in_array("ABMbancos",$this->token_valid->permissions))
				return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

      $post = json_decode(file_get_contents('php://input'));

      $bank_code        = $post->bank_code;
      $corporate_name   = $post->corporate_name;
      $address          = $post->address;
      $location         = $post->location;
      $phone_number     = $post->phone_number;
      $id								= (int) $this->get('id');

      if(empty($bank_code))        return $this->response(array('error'=>'No se ha ingresado codigo de banco'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($corporate_name))   return $this->response(array('error'=>'No se ha ingresado razon social'), REST_Controller::HTTP_BAD_REQUEST);

      //Valid repeated bank code
      $error = $this->Bank->validateDataOnUpdate($bank_code,$id);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

			//If everything is valid, update the user
      if($this->Bank->update($bank_code,$corporate_name,$address,$location,$phone_number,$id)){
        return $this->response(array('msg'=>'Banco modificado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}

    }

    //Show specific bank
    public function getBank_get(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

			//Validates if the user has permissions to do this action
			if(!in_array("ABMbancos",$this->token_valid->permissions))
				return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

      $id = $this->get('id');

      if(empty($id)) return $this->response(array('error'=>'Falta el ID del banco'), REST_Controller::HTTP_BAD_REQUEST);

      $bank = $this->Bank->getBankById($id);

      if(empty($bank)){
        return $this->response(array('error'=>'No se encontro el ID del banco'), REST_Controller::HTTP_BAD_REQUEST);
      } else {
        return $this->response($user, REST_Controller::HTTP_OK);
      }
    }

    //Delete bank
    public function removeBank_delete(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

			//Validates if the user has permissions to do this action
			if(!in_array("ABMbancos",$this->token_valid->permissions))
				return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

      $id	= (int) $this->get('id');

      if($this->Bank->delete($id)){
        return $this->response(array('msg'=>'Banco eliminado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
        return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
