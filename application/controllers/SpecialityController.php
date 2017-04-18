<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class SpecialityController extends AuthController {

    protected $access = "ABMespecialidades";
    function __construct() {
        parent::__construct();
        $this->load->model('Speciality');
        $this->load->library('validator');
    }

    //Create speciality
    public function specialities_post() {

        //TODO extract to helper
        $post = json_decode(file_get_contents('php://input'));

        $speciality_code = $post->speciality_code     ?? "";
        $description     = $post->description         ?? "";

        if (empty($speciality_code)) return $this->response(array('error' => 'No se ha ingresado código de especialidad'), REST_Controller::HTTP_BAD_REQUEST);
        if (empty($description))     return $this->response(array('error' => 'No se ha ingresado descripcion'), REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validateSpecialityLength($speciality_code)) return $this->response(array('error'=>'El código ingresado es demasiado largo (máximo 2 digitos)'), REST_Controller::HTTP_BAD_REQUEST);

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

    //Show specialities
    public function specialities_get() {

        $specialities = $this->Speciality->getspecialities();
        return $this->response($specialities, REST_Controller::HTTP_OK);
    }

    //Update speciality information
    public function updateSpeciality_put() {

        $post = json_decode(file_get_contents('php://input'));
        $description     = $post->description     ?? "";
        $id              = (int)$this->get('id');

        if (empty($description))     return $this->response(array('error' => 'No se ha ingresado descripcion'), REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, update the speciality
        if ($this->Speciality->update($description, $id)) {
            return $this->response(array('msg' => 'Especialidad modificada satisfactoriamente'), REST_Controller::HTTP_OK);
        } else {
            return $this->response(array('error' => 'Error de base de datos'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Show specific speciality
    public function getSpeciality_get() {

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

        $id = (int)$this->get('id');

        $result = $this->Speciality->delete($id);
        if(strcmp($result, 1) != 0) return $this->response(array('error'=>$result), REST_Controller::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>'Especialidad eliminada satisfactoriamente'), REST_Controller::HTTP_OK);
    }
}
