<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class RoleController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('role');
		}

    //Show roles
    public function roles_get(){
      $roles = Role::getRoles();
      $this->response($roles, REST_Controller::HTTP_OK);
    }

    //Create role
    public function roles_post(){

      $name         = $this->post('name');
      $permissions  = $this->post('permissions');

      if($name === NULL) $this->response("Name is missing", REST_Controller::HTTP_BAD_REQUEST);

      $newRole = new Role($name,$permissions);

      $error = $newRole->validateData();

      if(!$error['valid']){

        $this->response($error['message'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

      } else {

        if($newRole->save()){
          $this->response("Role created succesfully", REST_Controller::HTTP_OK);
        } else {
          $this->response("Database error", REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

      }

    }

}
