<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class UserController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('user');
			$this->load->model('role');
      $this->load->library('hash');
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

      if($name === NULL)            $this->response("Name is missing", REST_Controller::HTTP_BAD_REQUEST);
      if($last_name === NULL)       $this->response("Last name is missing", REST_Controller::HTTP_BAD_REQUEST);
      if($email === NULL)           $this->response("Email is missing", REST_Controller::HTTP_BAD_REQUEST);
      if($document_type === NULL)   $this->response("Document type is missing", REST_Controller::HTTP_BAD_REQUEST);
      if($document_number === NULL) $this->response("Document number is missing", REST_Controller::HTTP_BAD_REQUEST);
      if($password === NULL)        $this->response("Password is missing", REST_Controller::HTTP_BAD_REQUEST);

      $newUser = new User($name,$last_name,$document_type,$document_number,$email,$password,$roles);

      $error = $newUser->validateData();

      if(!$error['valid']){

        $this->response($error['message'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

      } else {

        if($newUser->save()){
          $this->response("User created succesfully", REST_Controller::HTTP_OK);
        } else {
          $this->response("Database error", REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

      }

    }

    //Update user information
    public function update_put(){

			/*$name             = $this->post('name');
      $last_name        = $this->post('last_name');
      $email            = $this->post('email');
      $document_type    = $this->post('document_type');
      $document_number  = $this->post('document_number');
      $roles            = $this->post('roles');

			if($name === NULL)            $this->response("Name is missing", REST_Controller::HTTP_BAD_REQUEST);
			if($last_name === NULL)       $this->response("Last name is missing", REST_Controller::HTTP_BAD_REQUEST);
			if($email === NULL)           $this->response("Email is missing", REST_Controller::HTTP_BAD_REQUEST);
			if($document_type === NULL)   $this->response("Document type is missing", REST_Controller::HTTP_BAD_REQUEST);
			if($document_number === NULL) $this->response("Document number is missing", REST_Controller::HTTP_BAD_REQUEST);*/



    }

    //Delete user
    public function remove_delete(){
      echo "remove delete";
    }

    //Show users
    public function getUsers_get(){
      $users = User::getUsers();
      $this->response($users, REST_Controller::HTTP_OK);
    }



}
