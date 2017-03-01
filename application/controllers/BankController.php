<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class UserController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('Bank');
		}

    //Create bank
    public function banks_post(){

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
      $banks = $this->Bank->getBanks();
      return $this->response($banks, REST_Controller::HTTP_OK);
    }

    //Update bank information
    public function updateBank_put(){
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

      $id	= (int) $this->get('id');

      if($this->Bank->delete($id)){
        return $this->response(array('msg'=>'Banco eliminado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
        return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
