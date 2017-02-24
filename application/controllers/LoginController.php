<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class LoginController extends REST_Controller{


		function __construct(){
			parent::__construct();
			$this->load->library('session');
			$this->load->library('hash');
			$this->load->model('login_model');
		}
	
		public function login_get(){
			echo "Test";
		}
		
		public function login_post(){
			$username = $this->post('username');
			$password = $this->post('password');

//			echo $this->hash->encrypt($password);
//			exit;

			$user_data = $this->login_model->getUser($username);

			if(!$user_data){
				return $this->set_response([
					'usuario' => $username,
					'message' => 'Usuario inexistente'
				], REST_Controller::HTTP_BAD_REQUEST);
			}

			if(!password_verify($password, $user_data->password)){
				return  $this->set_response([
					'usuario' => $username,
					'message' => 'Usuario  o Clave inexistente'
				], REST_Controller::HTTP_BAD_REQUEST);
			}

			//echo 'Creaate session';
			$this->create_session($user_data->email);

		}

		public function create_session($user){

		//$_SESSION["usuario"] = "test";

			return  $this->set_response([
					'session' => $_SESSION

			], REST_Controller::HTTP_ACCEPTED );
		}



}