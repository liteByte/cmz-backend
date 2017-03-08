<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';
//include (APPPATH . '/helpers/permissions.php');

// use namespace
use Restserver\Libraries\REST_Controller;

class UserController extends AuthController{

	private $token_valid;

	function __construct(){
		parent::__construct();
		$this->load->model('User');
		$this->load->model('Role');
		$this->load->helper('email');
	  $this->load->library('email');
		$this->load->library('validator');
		$this->token_valid = $this->validateToken(apache_request_headers());
	}

  //Create user
  public function users_post(){

		//Validates if the user is logged and the token sent is valid.
		if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

		//Validates if the user has permissions to do this action
		if(!in_array("ABMusuarios",$this->token_valid->permissions))
			return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_FORBIDDEN);

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

		//Validates if the user is logged and the token sent is valid.
		if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

		//Validates if the user has permissions to do this action
		if(!in_array("ABMusuarios",$this->token_valid->permissions))
			return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_FORBIDDEN);

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

		//Validates if the user is logged and the token sent is valid.
		if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

		//Validates if the user has permissions to do this action
		if(!in_array("ABMusuarios",$this->token_valid->permissions))
			return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_FORBIDDEN);

		$id	= (int) $this->get('id');

		if($this->User->delete($id,$this->token_valid->user_id)){
			return $this->response(array('msg'=>'Usuario eliminado satisfactoriamente'), REST_Controller::HTTP_OK);
		} else {
			return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

  }

  //Show users
  public function users_get(){

		//Validates if the user is logged and the token sent is valid.
		if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

		//Validates if the user has permissions to do this action
		if(!in_array("ABMusuarios",$this->token_valid->permissions))
			return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_FORBIDDEN);

    $users = $this->User->getUsers();
    return $this->response($users, REST_Controller::HTTP_OK);
  }

	//Show specific users
	public function getUser_get(){

		//Validates if the user is logged and the token sent is valid.
		if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

		//Validates if the user has permissions to do this action
		if(!in_array("ABMusuarios",$this->token_valid->permissions))
			return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_FORBIDDEN);

		//Validates if the user is logged and the token sent is valid.
		if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

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

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

			$post = json_decode(file_get_contents('php://input'));

			$document_type    = $post->document_type;
			$document_number  = $post->document_number;

			if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
			if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if(!$this->validator->validateDocument($document_type,$document_number)) 	return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

			//Verify if the user with specified document exits. If so, change it's password and send it by email
			$info = $this->User->getUserByDocument($document_type,$document_number);

			if($info['status'] != "ok") return $this->response(array('error'=>$info['data']), REST_Controller::HTTP_BAD_REQUEST);

			$newPassword = $this->generatePassword();
			if($this->User->changePassword($info['data']->user_id,$newPassword)){
				$this->sendMail($info, $newPassword);
			} else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}
		}

	//Change user password
	public function changePassword_post(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

			$post = json_decode(file_get_contents('php://input'));

			$oldPassword = $post->oldPassword;
			$newPassword = $post->newPassword;

			if(empty($oldPassword)) return $this->response(array('error'=>'No ha ingresado la contraseña antigua'), REST_Controller::HTTP_BAD_REQUEST);
			if(empty($newPassword)) return $this->response(array('error'=>'No ha ingresado la contraseña nueva'), REST_Controller::HTTP_BAD_REQUEST);

			//Validations
			if(!$this->validator->validatePassword($newPassword)) return $this->response(array('error'=>'Su contraseña debe tener 8 o mas digitos'), REST_Controller::HTTP_BAD_REQUEST);

			$user = $this->User->getUserById($this->token_valid->user_id);

			if(password_verify($oldPassword,$user->password)){
				if($this->User->changePassword($this->token_valid->user_id,$newPassword)){
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

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_UNAUTHORIZED);

			//Validates if the user has permissions to do this action
			if(!in_array("ABMusuarios",$this->token_valid->permissions))
				return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_FORBIDDEN);

			$post = json_decode(file_get_contents('php://input'));

			$roles = $post->roles;
			$id 	 = $this->get('id');

			if($this->User->updateRoles($roles,$id)){
				return $this->response(array('msg'=>'Roles del usuario actualizados correctamente'), REST_Controller::HTTP_OK);
			} else {
				return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}

		}

	//Generate Password
	public function generatePassword() {
		$password = '';
		$longitud = 8;
		$pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+';
		$max = strlen($pattern)-1;
		for($i=0;$i < $longitud;$i++) $password .= $pattern{mt_rand(0,$max)};
		return $password;
	}

	// Send Mail with new Password
	public function sendMail($info, $newPassword){

		$data['password']= $newPassword;
		$data['name']= $info['data']->name;

		$this->email->from('pruebalitebyte@gmail.com', 'CMZ');
		$this->email->to($info['data']->email);
		$this->email->subject('Recuperacion de contraseña');
		$this->email->message($this->load->view('email/recover_password', $data, true) );
		$this->email->set_mailtype('html');

		if($this->email->send()){
			return $this->response(array('msg'=>$info['data']->email), REST_Controller::HTTP_OK);
		} else {
			return $this->response(array('error'=>show_error($this->email->print_debugger())), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

	}

}
