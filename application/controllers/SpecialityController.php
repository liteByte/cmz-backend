<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class SpecialityController extends AuthController {

    private $token_valid;

    function __construct() {
        parent::__construct();
        $this->load->model('Speciality');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create speciality
    public function specialitys_post() {

        //TODO figure out a way to do this for all endpoints
        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

        //TODO extract to helper
        //Validates if the user has permissions to do this action
        if (!in_array("ABMespecialidades", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

        //TODO extract to helper
        $post = json_decode(file_get_contents('php://input'));

        $speciality_code = $post->speciality_code ?? "";
        $description = $post->description         ?? "";

        if (empty($speciality_code)) return $this->response(array('error' => 'No se ha ingresado codigo de especialidad'), REST_Controller::HTTP_BAD_REQUEST);
        if (empty($description)) return $this->response(array('error' => 'No se ha ingresado descripcion'), REST_Controller::HTTP_BAD_REQUEST);

        //Valid repeated speciality code
        $error = $this->Speciality->validateData($speciality_code);

        if (strcmp($error, "OK") != 0) return $this->response(array('error' => $error), REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, save the speciality
        if ($this->Speciality->save($speciality_code, $description)) {
            return $this->response(array('msg' => 'Especialidad creada satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error' => 'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Show specialitys
    public function specialitys_get() {

        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

        //Validates if the user has permissions to do this action
        if (!in_array("ABMespecialidades", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

        $specialitys = $this->Speciality->getSpecialitys();
        return $this->response($specialitys, REST_Controller::HTTP_OK);
    }

    //Update speciality information
    public function updateSpeciality_put() {

        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

        //Validates if the user has permissions to do this action
        if (!in_array("ABMbancos", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

        $post = json_decode(file_get_contents('php://input'));

        $speciality_code = $post->speciality_code ?? "";
        $description = $post->description     ?? "";
        $id = (int)$this->get('id');

        if (empty($speciality_code)) return $this->response(array('error' => 'No se ha ingresado codigo de especialidad'), REST_Controller::HTTP_BAD_REQUEST);
        if (empty($description)) return $this->response(array('error' => 'No se ha ingresado descripcion'), REST_Controller::HTTP_BAD_REQUEST);

        //Valid repeated speciality code
        $error = $this->Speciality->validateDataOnUpdate($speciality_code, $id);

        if (strcmp($error, "OK") != 0) return $this->response(array('error' => $error), REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, update the speciality
        if ($this->Speciality->update($speciality_code, $description, $id)) {
            return $this->response(array('msg' => 'Especialidad modificada satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error' => 'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Show specific speciality
    public function getSpeciality_get() {

        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

        //Validates if the user has permissions to do this action
        if (!in_array("ABMespecialidades", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

        $id = $this->get('id');

        if (empty($id)) return $this->response(array('error' => 'Falta el ID de la especialidad'), REST_Controller::HTTP_BAD_REQUEST);

        $speciality = $this->Speciality->getSpecialityById($id);

        if (empty($speciality)) {
            return $this->response(array('error' => 'No se encontro el ID de la especialidad'), REST_Controller::HTTP_BAD_REQUEST);
        } else {
            return $this->response($speciality, REST_Controller::HTTP_OK);
        }
    }

    //Delete speciality
    public function removeSpeciality_delete() {

        //Validates if the user is logged and the token sent is valid.
        if ($this->token_valid->status != "ok") return $this->response(array('error' => $this->token_valid->message), REST_Controller::HTTP_BAD_REQUEST);

        //Validates if the user has permissions to do this action
        if (!in_array("ABMespecialidades", $this->token_valid->permissions))
            return $this->response(array('error' => 'No tiene los permisos para realizar esta accion'), REST_Controller::HTTP_UNAUTHORIZED);

        $id = (int)$this->get('id');

        if ($this->Speciality->delete($id)) {
            return $this->response(array('msg' => 'Especialidad eliminada satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error' => 'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
