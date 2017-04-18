<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class RoleController extends AuthController {

    private $token_valid;
    protected $access = "*";
    function __construct() {
        parent::__construct();
        $this->load->model('Role');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Show roles
    public function roles_get() {
        $roles = $this->Role->getRoles();
        return $this->response($roles, REST_Controller::HTTP_OK);
    }

    //Create role
    public function roles_post() {
        
        $post = json_decode(file_get_contents('php://input'));

        $name = $post->name         ?? "";
        $permissions = $post->permissions  ?? array();

        if (empty($name)) return $this->response(array('error' => 'No se ha ingresado nombre'), REST_Controller::HTTP_BAD_REQUEST);

        $error = $this->Role->validateData($name);
        if (strcmp($error, "OK") != 0) return $this->response(array('error' => $error), REST_Controller::HTTP_BAD_REQUEST);

        if ($this->Role->save($name, $permissions)) {
            return $this->response(array('msg' => 'Rol creado satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error' => "Error de base de datos"), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Updates a role's permissions
    public function updateRole_put() {

        $post = json_decode(file_get_contents('php://input'));
        $permissions = $post->permissions ?? array();
        $id = $this->get('id');

        if ($this->Role->updatePermissions($permissions, $id)) {
            return $this->response(array('msg' => 'Rol modificado satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error' => "Error de base de datos"), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
