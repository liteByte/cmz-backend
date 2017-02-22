<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class PermissionController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('permission');
		}

    //Show permissions
    public function permissions_get(){
      $permissions = Permission::getPermissions();
      $this->response($permissions, REST_Controller::HTTP_OK);
    }

    //Create permissions
    public function permissions_post(){

      $name = $this->post('name');

      if($name === NULL) $this->response("Name is missing", REST_Controller::HTTP_BAD_REQUEST);

      $newPermission = new Role($name);

      $error = $newPermission->validateData();

      if(!$error['valid']){

        $this->response($error['message'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

      } else {

        if($newPermission->save()){
          $this->response("Permission created succesfully", REST_Controller::HTTP_OK);
        } else {
          $this->response("Database error", REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

      }

    }

}
