<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class RoleController extends AuthController{

		private $token_valid;

		function __construct(){
			parent::__construct();
			$this->load->model('Role');
			$this->token_valid = $this->validateToken(apache_request_headers());
		}

    //Show roles
    public function roles_get(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

      $roles = $this->Role->getRoles();
      return $this->response($roles, REST_Controller::HTTP_OK);
    }

    //Create role
    public function roles_post(){

			//Validates if the user is logged and the token sent is valid.
			if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

			$post = json_decode(file_get_contents('php://input'));

      $name         = $post->name;
      $permissions  = $post->permissions;

      if(empty($name)) return $this->response(array('error'=>'No se ha ingresado nombre'), REST_Controller::HTTP_BAD_REQUEST);

      $error = $this->Role->validateData($name);

      if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      if($this->Role->save($name,$permissions)){
        return $this->response(array('msg'=>'Rol creado satisfactoriamente'), REST_Controller::HTTP_OK);
      } else {
        return $this->response(array('error'=>"Error de base de datos"), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
