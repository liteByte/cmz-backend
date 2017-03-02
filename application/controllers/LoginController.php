<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LoginController extends CI_Controller {

    private $dni;
    private $clave;

    function __construct(){
        parent::__construct();
        $this->load->library('hash');
        $this->load->library('Response_msg');
        $this->load->model('user');
    }

    public function login(){
        $methodHTTP    =  $this->input->method();
            if( strtolower($methodHTTP) != 'post'){
                $this->response_msg->setResponse(['error'=> 'Metodo no aceptado'],400);
            }

        $_POST = json_decode(file_get_contents('php://input'), true);

        if ($this->form_validation->run('login_validate') == FALSE){
            $this->response_msg->setResponse(validation_errors());
        }else{
            $this->dni    = $this->security->xss_clean(addslashes(strip_tags($this->input->post('document_number', TRUE))));
            $this->clave  = $this->security->xss_clean(addslashes(strip_tags($this->input->post('password', TRUE))));
            $user_data    = $this->user->getUser($this->dni);
            if(!$user_data){
                 $this->response_msg->setResponse(['error' =>'Usuario inexistente o dado de baja' ],400);
            }

            if(!password_verify($this->clave , $user_data->password )){
                $this->response_msg->setResponse(['error' =>'Clave incorrecta'],400);
            }

            $permissions = $this->user->getPermissions($user_data->document_number);

            unset($user_data->password);

             // Create Token
            $user_data->permissions = $permissions;
            $jwt = JWT::encode($user_data, '');
            $this->response_msg->setResponse([
                            'token' => $jwt,
                            'permissions'  => $permissions
            ], 200);
        }
    }
}
