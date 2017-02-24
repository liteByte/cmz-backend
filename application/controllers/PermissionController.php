<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class PermissionController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('Permission');
		}

    //Show permissions
    public function permissions_get(){
      $permissions = $this->Permission->getPermissions();
      $this->response($permissions, REST_Controller::HTTP_OK);
    }

    //Create permissions
    public function permissions_post(){

			$post = json_decode(file_get_contents('php://input'));

      $name = $post->name;

      if(empty($name)) return $this->response(array('error'=>"No se ha ingresado nombre"), REST_Controller::HTTP_BAD_REQUEST);

      $error = $this->Permission->validateData($name);

      if(strcmp($error,"OK")) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

      if($this->Permission->save($name)){
        $this->response(array('msg'=>"Permiso creado satisfactoriamente"), REST_Controller::HTTP_OK);
      } else {
        $this->response(array('error'=>"Error de base de datos"), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
