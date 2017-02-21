<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class UserController extends REST_Controller{

		function __construct(){
			parent::__construct();
			$this->load->model('user');
		}

    //Create user
    public function signup_post(){
      echo "post signup";
    }

    //Update user information
    public function update_put(){
      echo "put update";
    }

    //Delete user
    public function remove_delete(){
      echo "remove delete";
    }

    //Show users
    public function getUsers_get(){
      echo "remove delete";
    }


}
