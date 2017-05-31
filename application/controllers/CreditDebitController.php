<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class CreditDebitController extends AuthController{

    private $token_valid;
    protected $access = "ABMdebitocredito";
    function __construct(){
        parent::__construct();
        $this->load->model('CreditDebit');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Get credits or debits of a certain bill
    public function creditDebit_get(){

        $id_bill = $this->get('bill_id')   ?? "";
        $type    = $this->get('note_type') ?? "";

        //Validate if any obligatory field is missing
        if(empty($id_bill)) return $this->response(['error'=>'No se ha ingresado una factura'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($type))    return $this->response(['error'=>'No se ha ingresado el tipo de nota a realizar'], REST_Controller::HTTP_BAD_REQUEST);

        $creditsOrDebits = $this->CreditDebit->getCreditDebitsWithBillData($id_bill,$type);
        return $this->response($creditsOrDebits, REST_Controller::HTTP_OK);

    }

    //Create credit or debit
    public function creditDebit_post(){

        $post = json_decode(file_get_contents('php://input'));

        $medical_insurance_id = $post->medical_insurance_id ?? "";
        $id_bill              = $post->id_bill              ?? "";
        $type                 = $post->type                 ?? "";
        $id_professional_data = $post->id_professional_data ?? "";
        $period               = $post->period               ?? "";
        $nomenclator_id       = $post->nomenclator_id       ?? "";
        $value_honorary       = $post->value_honorary       ?? "";
        $value_expenses       = $post->value_expenses       ?? "";
        $quantity             = $post->quantity             ?? "";
        $concept_id           = $post->concept_id           ?? "";

        //Validate if any obligatory field is missing
        if(empty($medical_insurance_id))                        return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($id_bill))                                     return $this->response(['error'=>'No se ha ingresado factura'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($type))                                        return $this->response(['error'=>'No se han ingresado el tipo (crédito/débito)'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($id_professional_data))                        return $this->response(['error'=>'No se ha ingresado profesional'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($period))                                      return $this->response(['error'=>'No se han ingresado período'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($nomenclator_id))                              return $this->response(['error'=>'No se han ingresado datos del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($quantity))                                    return $this->response(['error'=>'No se ha ingresado cantidad'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($concept_id))                                  return $this->response(['error'=>'No se ha ingresado concepto de crédito/débito'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($value_honorary) && $value_honorary !== '0')   return $this->response(['error'=>'No se ha ingresado valor de honorarios'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($value_expenses) && $value_expenses !== '0')   return $this->response(['error'=>'No se ha ingresado valor de gastos'], REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validateDate($period))                return $this->response(['error'=>'Fecha del período inválida'], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, save the credit/debit
        $result = $this->CreditDebit->save($medical_insurance_id, $id_bill, $type, $id_professional_data, $period, $nomenclator_id, $value_honorary, $value_expenses, $quantity, $concept_id);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

    //Update a credit/debit
    public function creditDebit_put(){

        $post = json_decode(file_get_contents('php://input'));

        $credit_debit_id      = (int) $this->get('id');
        $value_honorary       = $post->value_honorary       ?? "";
        $value_expenses       = $post->value_expenses       ?? "";
        $quantity             = $post->quantity             ?? "";
        $concept_id           = $post->concept_id           ?? "";

        //Validate if any obligatory field is missing
        if(empty($credit_debit_id))                             return $this->response(['error'=>'No se ha informado el crédito/débito que se quiere modificar'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($quantity))                                    return $this->response(['error'=>'No se ha ingresado cantidad'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($concept_id))                                  return $this->response(['error'=>'No se ha ingresado concepto de crédito/débito'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($value_honorary) && $value_honorary !== '0')   return $this->response(['error'=>'No se ha ingresado valor de honorarios'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($value_expenses) && $value_expenses !== '0')   return $this->response(['error'=>'No se ha ingresado valor de gastos'], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, update the credit/debit
        $result = $this->CreditDebit->update($value_honorary, $value_expenses, $quantity, $concept_id,$credit_debit_id);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

    //Real delete of a credit/debit
    public function creditDebit_delete(){

        $credit_debit_id  = (int) $this->get('id');

        //Validate if any obligatory field is missing
        if(empty($credit_debit_id)) return $this->response(['error'=>'No se ha informado el crédito/débito que se quiere modificar'], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, delete the credit/debit
        $result = $this->CreditDebit->delete($credit_debit_id);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

    //Delete all credit/debits of a bill
    public function deleteAll_delete(){

        $id_bill  = (int) $this->get('bill_id') ?? "";
        $type     = $this->get('type')          ?? "";

        //Validate if any obligatory field is missing
        if(empty($id_bill)) return $this->response(['error'=>'No se ha informado la factura cuyos créditos/debitos se quieren eliminar'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($type))    return $this->response(['error'=>'No se ha informado que elementos se quieren eliminar (créditos o débitos)'], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, delete the credit/debit
        $result = $this->CreditDebit->deleteAll($id_bill,$type);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

    //Validate a benefit with professional + period + nomenclator exists in the bill
    public function validateCreditDebitExistence_post(){

        $post = json_decode(file_get_contents('php://input'));

        $id_bill              = $post->id_bill              ?? "";
        $id_professional_data = $post->id_professional_data ?? "";
        $period               = $post->period               ?? "";
        $nomenclator_id       = $post->nomenclator_id       ?? "";

        //Validate if any obligatory field is missing
        if(empty($id_bill))                 return $this->response(['error'=>'No se ha ingresado factura'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($id_professional_data))    return $this->response(['error'=>'No se ha ingresado profesional'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($period))                  return $this->response(['error'=>'No se han ingresado período'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($nomenclator_id))          return $this->response(['error'=>'No se han ingresado datos del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validateDate($period)) return $this->response(['error'=>'Fecha del período inválida'], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, delete the credit/debit
        $result = $this->CreditDebit->validateExistence($id_bill, $id_professional_data, $period, $nomenclator_id);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }



}
