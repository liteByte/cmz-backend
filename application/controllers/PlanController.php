<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class PlanController extends AuthController{

    private $token_valid;
    protected $access = "ABMplanes";
    function __construct(){
        parent::__construct();
        $this->load->model('Plan');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create plan
    public function plans_post(){

        $post = json_decode(file_get_contents('php://input'));
        $description                  = $post->description               ?? "";
        $medical_insurance_id         = $post->medical_insurance_id      ?? "";

        if(empty($description))               return $this->response(['error'=>'No se ha ingresado descripción'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($medical_insurance_id))      return $this->response(['error'=>'No se ha ingresado el ID de la obra social'], REST_Controller::HTTP_BAD_REQUEST);

        //Valid repeated description
        $error = $this->Plan->validateData($description, $medical_insurance_id);

        if(strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, save the plan
        if($this->Plan->save($description, $medical_insurance_id)){
            return $this->response(['msg'=>'Plan creado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Show plans from a certain medical insurance
    public function plansByInsurance_get(){

        $medicalInsuranceID = (int) $this->get('id');
        $plans = $this->Plan->getPlansByInsuranceID($medicalInsuranceID);
        return $this->response($plans, REST_Controller::HTTP_OK);
    }

    //Update plan information
    public function updatePlan_put(){

        $post = json_decode(file_get_contents('php://input'));
        $description                  = $post->description               ?? "";
        $medical_insurance_id         = $post->medical_insurance_id      ?? "";
        $id                           = (int) $this->get('id');

        if(empty($description))               return $this->response(['error'=>'No se ha ingresado descripción'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($medical_insurance_id))      return $this->response(['error'=>'No se ha ingresado el ID de la obra social'], REST_Controller::HTTP_BAD_REQUEST);

        //Valid repeated description
        $error = $this->Plan->validateDataOnUpdate($description, $medical_insurance_id, $id);

        if(strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, update the plan
        if($this->Plan->update($description, $medical_insurance_id, $id, $this->token_valid->user_id)){
            return $this->response(['msg'=>'Plan modificado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Show specific plan
    public function getPlan_get(){

        $id = $this->get('id');

        if(empty($id)) return $this->response(['error'=>'Falta el ID del plan'], REST_Controller::HTTP_BAD_REQUEST);

        $plan = $this->Plan->getPlanById($id);

        if(empty($plan)){
            return $this->response(['error'=>'No se encontro el ID del plan'], REST_Controller::HTTP_BAD_REQUEST);
        } else {
            return $this->response($plan, REST_Controller::HTTP_OK);
        }
    }

    //Delete plan
    public function removePlan_delete(){
        $id = (int) $this->get('id');
        $result = $this->Plan->delete($id, $this->token_valid->user_id);
        if(strcmp($result, 1) != 0) return $this->response(array('error'=>$result), REST_Controller::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>'Plan eliminado satisfactoriamente'), REST_Controller::HTTP_OK);
    }
}
