<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class LoginController extends REST_Controller{
	
		function __construct(){
			parent::__construct();
			$this->load->library('hash');
			$this->load->model('login_model');
		}
	
		public function login_get(){
			echo "Test";
		}
		
		public function login_post(){
			
			$username = $this->post('username');
			$password = $this->post('password');

			$user_data = $this->login_model->getUser($username);
			
			if(!$user_data){
				return $this->set_response([
					'usuario' => $username,
					'message' => 'Usuario inexistente'
				], REST_Controller::HTTP_BAD_REQUEST);
			}

			if(password_verify($password, $user_data->password)){
				//$_SESSION['userid'] = $user_data->idu;
				echo $user_data->password;

			}

			
			
			
			
		}
	
}