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

			$post = json_decode(file_get_contents('php://input'));

      $name             = $post->name;
      $last_name        = $post->last_name;
      $email            = $post->email;
      $document_type    = $post->document_type;
      $document_number  = $post->document_number;
      $roles            = $post->roles;
      $password         = $post->password;

      if(empty($name))            return $this->response(array('error'=>'No se ha ingresado nombre'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($last_name))       return $this->response(array('error'=>'No se ha ingresado apellido'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($email))           return $this->response(array('error'=>'No se ha ingresado email'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($password))        return $this->response(array('error'=>'No se ha ingresado contraseña'), REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if (!valid_email($email)) 	return $this->response(array('error'=>'El formato de email no es correcto'), REST_Controller::HTTP_BAD_REQUEST);
      $error = $this->User->validateData($email,$document_number,$document_type);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

			//If everything is valid, save the user
      if($this->User->save($name,$last_name,$document_type,$document_number,$email,$password,$roles)){
        return $this->response(array('msg'=>'Usuario creado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}

    }

    //Update user information
    public function updateUser_put(){

			$post = json_decode(file_get_contents('php://input'));

      $name             = $post->name;
      $last_name        = $post->last_name;
      $email            = $post->email;
      $document_type    = $post->document_type;
      $document_number  = $post->document_number;
      $roles            = $post->roles;
			$id								= (int) $this->get('id');

			if(empty($name))            return $this->response(array('error'=>'No se ha ingresado nombre'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($last_name))       return $this->response(array('error'=>'No se ha ingresado apellido'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($email))           return $this->response(array('error'=>'No se ha ingresado email'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if (!valid_email($email)) 	return $this->response(array('error'=>'El formato de email no es correcto'), REST_Controller::HTTP_BAD_REQUEST);
			$error = $this->User->validateDataOnUpdate($email,$document_number,$document_type,$id);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

			//If everything is valid, update the user
      if($this->User->update($name,$last_name,$document_type,$document_number,$email,$id,$roles)){
        return $this->response(array('msg'=>'Usuario modificado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}


    }

    //Delete user
    public function removeUser_delete(){

			$id	= (int) $this->get('id');

			if($this->User->delete($id)){
				return $this->response(array('msg'=>'Usuario eliminado satisfactoriamente'), REST_Controller::HTTP_OK);
			} else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}


    }

    //Show users
    public function getUsers_get(){
      $users = $this->User->getUsers();
      return $this->response($users, REST_Controller::HTTP_OK);
    }

		//Show users
		public function getUser_get(){

			$id = $this->get('id');

      if(empty($id)) return $this->response(array('error'=>'Falta el ID del usuario'), REST_Controller::HTTP_BAD_REQUEST);

			$user = $this->User->getUserById($id);

			if(empty($user)){
				return $this->response(array('error'=>'No se encontro el ID del usuario'), REST_Controller::HTTP_BAD_REQUEST);
			} else {
				return $this->response($user, REST_Controller::HTTP_OK);
			}
		}



}
