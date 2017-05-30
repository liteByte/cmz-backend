<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class CreditDebitConceptController extends AuthController{

    private $token_valid;
    protected $access = "ABMconceptosdebitocredito";
    function __construct(){
        parent::__construct();
        $this->load->model('CreditDebitConcept');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create concept
    public function concepts_post(){
        $post = json_decode(file_get_contents('php://input'));

        $code                   = $post->code                 ?? "";
        $concept_description    = $post->concept_description  ?? "";
        $concept_group_id       = $post->concept_group_id     ?? "";
        $concept_type_id        = $post->concept_type_id      ?? "";
        $concept_movement_id    = $post->concept_movement_id  ?? "";
        $value                  = $post->value                ?? "";
        $applies_liquidation    = $post->applies_liquidation  ?? "";
        $receipt_legend         = $post->receipt_legend       ?? "";

        if (empty($code))                          return $this->response(['error'=>'No se ha ingresado código'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_description))           return $this->response(['error'=>'No se ha ingresado descripción del concepto'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_group_id))              return $this->response(['error'=>'No se ha ingresado grupo'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_type_id))               return $this->response(['error'=>'No se ha ingresado tipo de concepto'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_movement_id))           return $this->response(['error'=>'No se ha ingresado tipo de movimiento'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($value))                         return $this->response(['error'=>'No se ha ingresado valor'], REST_Controller::HTTP_BAD_REQUEST);
        if (strlen($applies_liquidation) <> 1)     return $this->response(['error'=>'No se ha informado si aplica en liquidación'], REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if ($code == 0)      return $this->response(['error'=>'El código de concepto no puede ser 0'], REST_Controller::HTTP_BAD_REQUEST);
        if ($value < 0)      return $this->response(['error'=>'El valor ingresado no puede ser negativo'], REST_Controller::HTTP_BAD_REQUEST);

        //Validate ids and repeated code
        $error = $this->CreditDebitConcept->validateData($code, $concept_group_id,$concept_type_id,$concept_movement_id);

        if (strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, save the concept
        if ($this->CreditDebitConcept->save($code, $concept_description, $concept_group_id,$concept_type_id,$concept_movement_id,$value,$applies_liquidation,$receipt_legend)){
            return $this->response(['msg'=>'Concepto creado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Show concepts
    public function concepts_get(){

        $id = $this->get('id');
        if (!isset($id)){
            $concepts = $this->CreditDebitConcept->getConcepts();
            return $this->response($concepts, REST_Controller::HTTP_OK);
        } else {
            $concept = $this->CreditDebitConcept->getConceptById($id);
            if (empty($concept)){
                return $this->response(['error'=>'No se encontro el ID del concepto'], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                return $this->response($concept, REST_Controller::HTTP_OK);
            }
        }
    }

    //Update concept information
    public function concepts_put(){

        $post = json_decode(file_get_contents('php://input'));

        $concept_description    = $post->concept_description  ?? "";
        $concept_group_id       = $post->concept_group_id     ?? "";
        $concept_type_id        = $post->concept_type_id      ?? "";
        $concept_movement_id    = $post->concept_movement_id  ?? "";
        $value                  = $post->value                ?? "";
        $applies_liquidation    = $post->applies_liquidation  ?? "";
        $receipt_legend         = $post->receipt_legend       ?? "";
        $id                     = (int) $this->get('id');

        if (empty($concept_description))           return $this->response(['error'=>'No se ha ingresado descripción del concepto'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_group_id))              return $this->response(['error'=>'No se ha ingresado grupo'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_type_id))               return $this->response(['error'=>'No se ha ingresado tipo de concepto'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($concept_movement_id))           return $this->response(['error'=>'No se ha ingresado tipo de movimiento'], REST_Controller::HTTP_BAD_REQUEST);
        if (empty($value))                         return $this->response(['error'=>'No se ha ingresado valor'], REST_Controller::HTTP_BAD_REQUEST);
        if (strlen($applies_liquidation) <> 1)     return $this->response(['error'=>'No se ha informado si aplica en liquidación'], REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if ($value < 0)      return $this->response(['error'=>'El valor ingresado no puede ser negativo'], REST_Controller::HTTP_BAD_REQUEST);

        //Valid ids
        $error = $this->CreditDebitConcept->validateIDs($concept_group_id,$concept_type_id,$concept_movement_id);

        if (strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, update the concept
        if ($this->CreditDebitConcept->update($concept_description, $concept_group_id, $concept_type_id, $concept_movement_id, $value, $applies_liquidation, $receipt_legend, $id, $this->token_valid->user_id)){
            return $this->response(['msg'=>'Concepto modificado satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Delete concept
    public function concepts_delete(){

        $id = (int) $this->get('id');
        $result = $this->CreditDebitConcept->delete($id, $this->token_valid->user_id);
        if (strcmp($result, 1) != 0) return $this->response(['error'=>$result], REST_Controller::HTTP_BAD_REQUEST);
        return $this->response(['msg'=>'Concepto eliminado satisfactoriamente'], REST_Controller::HTTP_OK);
    }

    //Autocomplete service for cdconcepts
    public function cdconceptData_get(){

        $description = $this->get('description') ?? "";
        $type        = $this->get('type')        ?? "";    //1-Credito , 2-Debito

        $cdConceptData = $this->CreditDebitConcept->getByDescriptionLike($description,$type);
        return $this->response($cdConceptData, REST_Controller::HTTP_OK);

    }

}
