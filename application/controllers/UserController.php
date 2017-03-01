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
		  $this->load->library('email');
			$this->load->library('validator');
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
			if(!$this->validator->validatePassword($password)) 										    return $this->response(array('error'=>'Su contraseña debe tener 8 o mas digitos'), REST_Controller::HTTP_BAD_REQUEST);
			if(!$this->validator->validateDocument($document_type,$document_number)) 	return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);
			if (!valid_email($email)) 																								return $this->response(array('error'=>'El formato de email no es correcto'), REST_Controller::HTTP_BAD_REQUEST);

			//Valid repeated email or document number
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
			if(!$this->validator->validateDocument($document_type,$document_number)) 	return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);
			if (!valid_email($email)) 																								return $this->response(array('error'=>'El formato de email no es correcto'), REST_Controller::HTTP_BAD_REQUEST);

			//Valid repeated email or document number
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

		//Send recovery mail to user
		public function recoverPassword_post(){

			$post = json_decode(file_get_contents('php://input'));

			$document_type    = $post->document_type;
      $document_number  = $post->document_number;

			if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
      if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if(!$this->validator->validateDocument($document_type,$document_number)) 	return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

			$info = $this->User->getUserByDocument($document_type,$document_number);

			if($info['status'] != "ok") return $this->response(array('error'=>$info['data']), REST_Controller::HTTP_BAD_REQUEST);

			$newPassword = random_bytes(8);
			if($this->User->changePassword($info['data']->user_id,$newPassword)){

				$this->email->from('cmz@cmz.com', 'CMZ');
				$this->email->to($info['data']->email);
				$this->email->subject('Recuperacion de contraseña');
				$this->email->message('Se nueva contraseña es: '.$newPassword);
				$this->email->send();

				return $this->response(array('msg'=>'Operacion satisfactoria: se ha enviado su nueva contraseña a su correo electronico'), REST_Controller::HTTP_OK);

			} else {

				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

			}

		}

		//Change user password
		public function changePassword_post(){

			$post = json_decode(file_get_contents('php://input'));

			$oldPassword = $post->oldPassword;
			$newPassword = $post->newPassword;
			$id 			   = $this->get('id');

			if(empty($oldPassword)) return $this->response(array('error'=>'No ha ingresado la contraseña antigua'), REST_Controller::HTTP_BAD_REQUEST);
			if(empty($newPassword)) return $this->response(array('error'=>'No ha ingresado la contraseña nueva'), REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if(!$this->validator->validatePassword($newPassword)) return $this->response(array('error'=>'Su contraseña debe tener 8 o mas digitos'), REST_Controller::HTTP_BAD_REQUEST);

			$user = $this->User->getUserById($id);

			if(password_verify($oldPassword,$user->password)){
				if($this->User->changePassword($id,$newPassword)){
					return $this->response(array('msg'=>"Se ha cambiado la contraseña satisfactoriamente"), REST_Controller::HTTP_OK);
				} else {
					return $this->response(array('error'=>'Error al realizar el cambio de contraseña'), REST_Controller::HTTP_BAD_REQUEST);
				}
			} else {
				return $this->response(array('error'=>'La contraseña antigua es incorrecta'), REST_Controller::HTTP_BAD_REQUEST);
			}

			return $this->response(array('msg'=>$user->password), REST_Controller::HTTP_OK);

		}

		//Update a user specific roles
		public function updateRoles_post(){

			$post = json_decode(file_get_contents('php://input'));

			$roles = $post->roles;
			$id 	 = $this->get('id');

			if($this->User->updateRoles($roles,$id)){
				return $this->response(array('msg'=>'Roles del usuario actualizados correctamente'), REST_Controller::HTTP_OK);
			} else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}

		}

}
