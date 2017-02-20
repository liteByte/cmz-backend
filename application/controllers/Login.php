<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class Login extends REST_Controller{
	
		function __construct(){
			parent::__construct();
			$this->load->model('login_model');
		}
	
		public function login_get(){
			echo "get";
		}
		
		public function login_post(){
			
			$email = $this->post('email');
			$data = $this->login_model->getUser($email);
			
			if($data){
				$this->response($data);
			}
		}
	
}