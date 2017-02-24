<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
//require_once(APPPATH.'/models/Role.php');

// use namespace
use Restserver\Libraries\REST_Controller;

class RoleController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('Role');
		}

    //Show roles
    public function roles_get(){
      $roles = $this->Role->getRoles();
      return $this->response($roles, REST_Controller::HTTP_OK);
    }

    //Create role
    public function roles_post(){

      $name         = $this->post('name');
      $permissions  = $this->post('permissions');

      if(empty($name)) return $this->response("Name is missing", REST_Controller::HTTP_BAD_REQUEST);

      $error = $this->Role->validateData($name);

      if(strcmp($error,"OK") != 0) return $this->response($error, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

      if($this->Role->save($name,$permissions)){
        return $this->response("Role created succesfully", REST_Controller::HTTP_OK);
      } else {
        return $this->response("Database error", REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }

    }

}
