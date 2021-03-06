<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class UserController extends AuthController{

    private $token_valid;
    protected $access = "ABMusuarios";
    function __construct(){
        parent::__construct();
        $this->load->model('User');
        $this->load->model('Role');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create user
    public function users_post(){

        $post = json_decode(file_get_contents('php://input'));
        $name             = $post->name             ?? "";
        $last_name        = $post->last_name        ?? "";
        $email            = $post->email            ?? "";
        $document_type    = $post->document_type    ?? "";
        $document_number  = $post->document_number  ?? "";
        $roles            = $post->roles            ?? array();
        $password         = $post->password         ?? "";

        if(empty($name))            return $this->response(array('error'=>'No se ha ingresado nombre'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($last_name))       return $this->response(array('error'=>'No se ha ingresado apellido'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($email))           return $this->response(array('error'=>'No se ha ingresado email'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($password))        return $this->response(array('error'=>'No se ha ingresado contraseña'), REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validatePassword($password))                           return $this->response(array('error'=>'Su contraseña debe tener 8 o mas digitos'), REST_Controller::HTTP_BAD_REQUEST);
        if(!$this->validator->validateDocument($document_type,$document_number))     return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);
        if (!valid_email($email))                                                    return $this->response(array('error'=>'El formato de email no es correcto'), REST_Controller::HTTP_BAD_REQUEST);

        //Valid repeated email or document number
        $error = $this->User->validateData($email,$document_number,$document_type);

        if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, save the user
        if($this->User->save($name,$last_name,$document_type,$document_number,$email,$password,$roles)){
            SendMail::signin($name, $document_number, $email, $password);
            return $this->response(array('msg'=>'Usuario creado satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Update user information
    public function updateUser_put(){

        $post = json_decode(file_get_contents('php://input'));
        $name             = $post->name              ?? "";
        $last_name        = $post->last_name         ?? "";
        $email            = $post->email             ?? "";
        $document_type    = $post->document_type     ?? "";
        $document_number  = $post->document_number   ?? "";
        $roles            = $post->roles             ?? array();
        $id               = (int) $this->get('id');

        if(empty($name))            return $this->response(array('error'=>'No se ha ingresado nombre'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($last_name))       return $this->response(array('error'=>'No se ha ingresado apellido'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($email))           return $this->response(array('error'=>'No se ha ingresado email'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validateDocument($document_type,$document_number))     return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);
        if (!valid_email($email))                                                    return $this->response(array('error'=>'El formato de email no es correcto'), REST_Controller::HTTP_BAD_REQUEST);

        //Valid repeated email or document number
        $error = $this->User->validateDataOnUpdate($email,$document_number,$document_type,$id);

        if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, update the user
        if($this->User->update($name,$last_name,$document_type,$document_number,$email,$id,$roles)){
            return $this->response(array('msg'=>'Usuario modificado satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Delete user
    public function removeUser_delete(){

        $id = (int) $this->get('id');
        $result = $this->User->delete($id,$this->token_valid->user_id);

        if(strcmp($result, 1) != 0) return $this->response(array('error'=>$result), REST_Controller::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>'Usuario eliminado satisfactoriamente'), REST_Controller::HTTP_OK);
    }

    //Show users
    public function users_get(){

        $users = $this->User->getUsers();
        return $this->response($users, REST_Controller::HTTP_OK);
    }

    //Show specific users
    public function getUser_get(){

        $id = $this->get('id');
        if(empty($id)) return $this->response(array('error'=>'Falta el ID del usuario'), REST_Controller::HTTP_BAD_REQUEST);
        $user = $this->User->getUserById($id);

        if(empty($user)){
            return $this->response(array('error'=>'No se encontro el ID del usuario'), REST_Controller::HTTP_BAD_REQUEST);
        } else {
            return $this->response($user, REST_Controller::HTTP_OK);
        }
    }

    //Send recovery mail to user
    public function recoverPassword_post(){

        $post = json_decode(file_get_contents('php://input'));

        $document_type    = $post->document_type       ?? "";
        $document_number  = $post->document_number     ?? "";

        if(empty($document_type))   return $this->response(array('error'=>'No se ha ingresado tipo de documento'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($document_number)) return $this->response(array('error'=>'No se ha ingresado numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validateDocument($document_type,$document_number))     return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), REST_Controller::HTTP_BAD_REQUEST);

        //Verify if the user with specified document exits. If so, change it's password and send it by email
        $info = $this->User->getUserByDocument($document_type,$document_number);

        if($info['status'] != "ok") return $this->response(array('error'=>$info['data']), REST_Controller::HTTP_BAD_REQUEST);

        $newPassword = $this->generatePassword();
        if($this->User->changePassword($info['data']->user_id,$newPassword)){
            SendMail::recoverpassword($info, $newPassword);
        } else {
            return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Change user password
    public function changePassword_post(){

        $post = json_decode(file_get_contents('php://input'));

        $oldPassword = $post->oldPassword ?? "";
        $newPassword = $post->newPassword ?? "";

        if(empty($oldPassword)) return $this->response(array('error'=>'No ha ingresado la contraseña antigua'), REST_Controller::HTTP_BAD_REQUEST);
        if(empty($newPassword)) return $this->response(array('error'=>'No ha ingresado la contraseña nueva'), REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validatePassword($newPassword)) return $this->response(array('error'=>'Su contraseña debe tener 8 o mas digitos'), REST_Controller::HTTP_BAD_REQUEST);

        $user = $this->User->getUserById($this->token_valid->user_id);

        if(password_verify($oldPassword,$user->password)){
            if($this->User->changePassword($this->token_valid->user_id,$newPassword)){
                return $this->response(array('msg'=>"Se ha cambiado la contraseña satisfactoriamente"), REST_Controller::HTTP_OK);
            } else {
                return $this->response(array('error'=>'Error al realizar el cambio de contraseña'), REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            return $this->response(array('error'=>'La contraseña antigua es incorrecta'), REST_Controller::HTTP_BAD_REQUEST);
        }

        return $this->response(array('msg'=>$user->password), REST_Controller::HTTP_OK);

    }

    //Update a user specific roles
    public function updateRoles_post(){

        $post = json_decode(file_get_contents('php://input'));
        $roles = $post->roles ?? array();
        $id    = $this->get('id');

        if($this->User->updateRoles($roles,$id)){
            return $this->response(array('msg'=>'Roles del usuario actualizados correctamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error'=>'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Generate Password
    public function generatePassword() {
        $password = '';
        $longitud = 8;
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+';
        $max = strlen($pattern)-1;
        for($i=0;$i < $longitud;$i++) $password .= $pattern{mt_rand(0,$max)};
        return $password;
    }
}
