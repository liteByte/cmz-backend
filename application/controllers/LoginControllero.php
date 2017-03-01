<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class LoginControllero extends AuthController{

    	function __construct(){
			parent::__construct();
			$this->load->library('session');
			$this->load->model('login_model');
            //$this->validateToken(apache_request_headers());
		}
	
		public function login_get(){ echo "Test"; }
		
		public function login_post(){
			$dni = $this->post('dni');
			$password = $this->post('clave');
            $user_data = $this->login_model->getUser($dni);

			if(!$user_data){
				return $this->set_response([
					'usuario' => $dni,
					'message' => 'Usuario inexistente'
				], REST_Controller::HTTP_BAD_REQUEST);
			}

			if(!password_verify($password, $user_data->password)){
				return  $this->set_response([
					'usuario' => $dni,
					'message' => 'Usuario  o Clave inexistente'
				], REST_Controller::HTTP_BAD_REQUEST);
			}
		}



}