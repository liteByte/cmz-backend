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
                $this->response_msg->setResponse(['mensaje'=> 'Metodo no aceptado']);
            }

        $_POST = json_decode(file_get_contents('php://input'), true);

        if ($this->form_validation->run('login_validate') == FALSE){
            $this->response_msg->setResponse(validation_errors());
        }else{
            $this->dni =  $this->security->xss_clean(addslashes(strip_tags($this->input->post('dni', TRUE))));
            $this->clave =  $this->security->xss_clean(addslashes(strip_tags($this->input->post('clave', TRUE))));
            $user_data = $this->user->getUser($this->dni);
            if(!$user_data){
                 $this->response_msg->setResponse(['mensaje' =>'Usuario inexistente' ]);
            }

            if(!password_verify($this->clave , $user_data->password )){
                $this->response_msg->setResponse(['mensaje' =>'Clave incorrecta']);
            }


            $permissions = $this->user->getPermissions($user_data->document_number);

            if(!$permissions){
                $this->response_msg->setResponse(['mensaje' =>'Usuario no tiene permisos asociados']);
            }
            unset($user_data->password);

             // Create Token
            $user_data->permissions = $permissions;
            $user_data->iat = time();
            $user_data->exp = time() + 300;
            $jwt = JWT::encode($user_data, '');
            $this->response_msg->setResponse([
                            'token' => $jwt,
                            'code'  => 0
            ], 200);
        }
    }
}