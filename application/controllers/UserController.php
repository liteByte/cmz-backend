<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class UserController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('User');
			$this->load->model('Role');
			$this->load->helper('email');
		}

    //Create user
    public function signup_post(){

      $name             = $this->post('name');
      $last_name        = $this->post('last_name');
      $email            = $this->post('email');
      $document_type    = $this->post('document_type');
      $document_number  = $this->post('document_number');
      $roles            = $this->post('roles');
      $password         = $this->post('password');

      if(empty($name))            return $this->response("No se ha ingresado nombre", REST_Controller::HTTP_BAD_REQUEST);
      if(empty($last_name))       return $this->response("No se ha ingresado apellido", REST_Controller::HTTP_BAD_REQUEST);
      if(empty($email))           return $this->response("No se ha ingresado email", REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_type))   return $this->response("No se ha ingresado tipo de documento", REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_number)) return $this->response("No se ha ingresado numero de documento", REST_Controller::HTTP_BAD_REQUEST);
      if(empty($password))        return $this->response("No se ha ingresado contraseña", REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if (!valid_email($email)) 	return $this->response("El formato de email no es correcto", REST_Controller::HTTP_BAD_REQUEST);
      $error = $this->User->validateData($email,$document_number,$document_type);

      if(strcmp($error,"OK") != 0) return $this->response($error, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

			//If everything is valid, save the user
      if($this->User->save($name,$last_name,$document_type,$document_number,$email,$password,$roles)){
        return $this->response("Usuario creado satisfactoriamente", REST_Controller::HTTP_OK);
      } else {
				return $this->response("Error de base de datos", REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}

    }

    //Update user information
    public function update_put(){

			$name             = $this->put('name');
			$last_name        = $this->put('last_name');
			$email            = $this->put('email');
			$document_type    = $this->put('document_type');
			$document_number  = $this->put('document_number');
			$roles            = $this->put('roles');
			$id								= $this->get('id');

			if(empty($name))            return $this->response("Name is missing", REST_Controller::HTTP_BAD_REQUEST);
			if(empty($last_name))       return $this->response("Last name is missing", REST_Controller::HTTP_BAD_REQUEST);
			if(empty($email))           return $this->response("Email is missing", REST_Controller::HTTP_BAD_REQUEST);
			if(empty($document_type))   return $this->response("Document type is missing", REST_Controller::HTTP_BAD_REQUEST);
			if(empty($document_number)) return $this->response("Document number is missing", REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if (!valid_email($email)) 	return $this->response("El formato de email no es correcto", REST_Controller::HTTP_BAD_REQUEST);
			$error = $this->User->validateData($email,$document_number,$document_type);

      if(strcmp($error,"OK") != 0) return $this->response($error, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

			if($this->User->update($name,$last_name,$document_type,$document_number,$email,$id,$roles)){
        return $this->response("User updated succesfully", REST_Controller::HTTP_OK);
      } else {
				return $this->response("Database error", REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}


    }

    //Delete user
    public function remove_delete(){
      echo "remove delete";
    }

    //Show users
    public function getUsers_get(){
      $users = $this->User->getUsers();
      return $this->response($users, REST_Controller::HTTP_OK);
    }

		//Show users
		public function getUser_get(){

			$id = $this->get('id');

      if(empty($id)) return $this->response("User ID is missing", REST_Controller::HTTP_BAD_REQUEST);

			$user = $this->User->getUserById($id);

			if(empty($user)){
				return $this->response("User ID not found", REST_Controller::HTTP_BAD_REQUEST);
			} else {
				return $this->response($user, REST_Controller::HTTP_OK);
			}
		}



}
