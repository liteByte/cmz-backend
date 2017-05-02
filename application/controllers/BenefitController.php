<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class BenefitController extends AuthController{

    private $token_valid;
    protected $access = "ABMprestaciones";

    function __construct(){
        parent::__construct();
        $this->load->model('benefit');
        $this->load->model('affiliate');
        $this->load->model('nomenclator');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create benefit
    public function benefits_post(){

        $post = json_decode(file_get_contents('php://input'));

        $medical_insurance_id               = $post->medical_insurance_id               ?? "";
        $plan_id                            = $post->plan_id                            ?? "";
        $id_professional_data               = $post->id_professional_data               ?? "";
        $period                             = $post->period                             ?? "";
        $remesa                             = $post->remesa                             ?? "";
        $additional                         = $post->additional                         ?? "";
        $nomenclator_id                     = $post->nomenclator_id                     ?? "";
        $quantity                           = $post->quantity                           ?? "";
        $billing_code_id                    = $post->billing_code_id                    ?? "";
        $multiple_operation_value           = $post->multiple_operation_value           ?? "";
        $holiday_option_id                  = $post->holiday_option_id                  ?? "";
        $maternal_plan_option_id            = $post->maternal_plan_option_id            ?? "";
        $internment_ambulatory_option_id    = $post->internment_ambulatory_option_id    ?? "";
        $unit_price                         = $post->unit_price                         ?? "";
        $affiliate_id                       = $post->affiliate_id                       ?? "";
        $benefit_date                       = $post->benefit_date                       ?? "";
        $affiliate_number                   = $post->affiliate_number                   ?? "";
        $affiliate_name                     = $post->affiliate_name                     ?? "";
        $bill_number                        = $post->bill_number                        ?? "";
        $modify_coverage                    = $post->modify_coverage                    ?? "";
        $new_honorary                       = $post->new_honorary                       ?? "";
        $new_expenses                       = $post->new_expenses                       ?? "";

        //Validate if any obligatory field is missing
        if(empty($medical_insurance_id))                return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($plan_id))                             return $this->response(['error'=>'No se ha ingresado plan'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($id_professional_data))                return $this->response(['error'=>'No se han ingresado datos del profesional'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($period))                              return $this->response(['error'=>'No se ha ingresado período'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($nomenclator_id))                      return $this->response(['error'=>'No se han ingresado datos del nomenclador'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($quantity) && $quantity !== '0')       return $this->response(['error'=>'No se ha ingresado cantidad'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($billing_code_id))                     return $this->response(['error'=>'No se ha ingresado código de facturación'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($multiple_operation_value))            return $this->response(['error'=>'No se ha ingresado porcentaje de operación múltiple'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($internment_ambulatory_option_id))     return $this->response(['error'=>'No se han ingresado datos de prestación con respecto a internación o ambulatoria'], REST_Controller::HTTP_BAD_REQUEST);


        //Validations
        if(!$this->validator->validateDate($period))                return $this->response(['error'=>'Fecha del período inválida'], REST_Controller::HTTP_BAD_REQUEST);


        //Validate optional fields (if sent)
        if(!empty($benefit_date)){
            if(!$this->validator->validateDate($benefit_date))      return $this->response(['error'=>'Fecha de prestación invalida'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($affiliate_number)){
            if(empty($affiliate_name))      return $this->response(['error'=>'Se debe informar tanto el nombre del afiliado como su número'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($affiliate_name)){
            if(empty($affiliate_number))    return $this->response(['error'=>'Se debe informar tanto el número del afiliado como su nombre'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if($modify_coverage == 1){
            if(empty($new_honorary) && $new_honorary !== '0')    return $this->response(['error'=>'Si se redefinen los porcentajes de cobertura, la nueva cobertura de honorarios no puede ser vacía'], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($new_expenses) && $new_expenses !== '0')    return $this->response(['error'=>'Si se redefinen los porcentajes de cobertura, la nueva cobertura de gastos no puede ser vacía'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($bill_number)){
            if($unit_price <= 0)            return $this->response(['error'=>'Si se ingreso un numero de factura, el precio unitario debe ser mayor a 0'], REST_Controller::HTTP_BAD_REQUEST);
            switch ($billing_code_id) { //1-Just honoraries - 2-Just expenses - 3-Both
                case 1:
                    $value_honorary = $unit_price;
                    $value_expenses = 0;
                    break;
                case 2:
                    $value_honorary = 0;
                    $value_expenses = $unit_price;
                    break;
                case 3:
                    $value_honorary = $unit_price;
                    $value_expenses = $unit_price;
                    break;
                default:
                    return $this->response(['error'=>'El código de facturación ingresado no existe'], REST_Controller::HTTP_BAD_REQUEST);
            }
        }


        //Validate fields and unique key
        $error = $this->benefit->validateData($medical_insurance_id, $plan_id, $id_professional_data, $period, $nomenclator_id);

        if(strcmp($error,"OK") != 0) return $this->response(['error'=>$error], REST_Controller::HTTP_BAD_REQUEST);


        //Validate additional field (depending on nomenclator)
        $nomenclator = $this->nomenclator->getNomenclatorById($nomenclator_id);
        if($nomenclator->surgery == 1 && empty($additional)) return $this->response(['error'=>'Se debe seleccionar obligatoriamente un elemento del campo cirugía debido al nomenclador seleccionado'], REST_Controller::HTTP_BAD_REQUEST);


        ////Create the affiliate if informed and if it does not exist
        $affiliateOperation = ["status" => "", "affiliate_id" => $affiliate_id];

        //If an affiliate ID was informed, check if it is valid
        if(!empty($affiliate_id)){
            $affiliate_number = "";
            $affiliate_name   = "";
            if (!$this->affiliate->validateID($affiliate_id)){
                return $this->response(['error' => 'El afiliado ingresado no existe en base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        //Check if an affiliate number was informed
        if (!empty($affiliate_number)) {
            //If a number was informed, check it's existence.
            if (!$this->affiliate->checkExistence($affiliate_number)) {
                $affiliateOperation = $this->affiliate->save($medical_insurance_id, $plan_id, $affiliate_number, $affiliate_name);
                if (!$affiliateOperation["status"]) {
                    return $this->response(['error' => 'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        //If everything is valid, save the benefit
        if($this->benefit->save($medical_insurance_id, $plan_id, $id_professional_data, $period, $remesa, $additional, $nomenclator_id, $quantity, $billing_code_id, $multiple_operation_value, $holiday_option_id, $maternal_plan_option_id, $internment_ambulatory_option_id, $unit_price, $benefit_date, $affiliateOperation["affiliate_id"], $bill_number, $modify_coverage, $new_honorary, $new_expenses,$value_honorary, $value_expenses)){
            return $this->response(['msg'=>'Prestación creada satisfactoriamente'], REST_Controller::HTTP_OK);
        } else {
            return $this->response(['error'=>'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //Show benefits
    public function benefits_get(){

        $id = $this->get('id');

        if (!isset($id)){

            $benefits = $this->benefit->getBenefits();
            return $this->response($benefits, REST_Controller::HTTP_OK);

        } else {

            $benefit = $this->benefit->getBenefitById($id);

            if(empty($benefit)){
                return $this->response(['error'=>'No se encontró el ID de la prestación'], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                return $this->response($benefit, REST_Controller::HTTP_OK);
            }

        }
    }

    //Update benefit information
    public function benefits_put(){

        $post = json_decode(file_get_contents('php://input'));

        $medical_insurance_id               = $post->medical_insurance_id               ?? "";
        $plan_id                            = $post->plan_id                            ?? "";
        $remesa                             = $post->remesa                             ?? "";
        $additional                         = $post->additional                         ?? "";
        $quantity                           = $post->quantity                           ?? "";
        $billing_code_id                    = $post->billing_code_id                    ?? "";
        $multiple_operation_value           = $post->multiple_operation_value           ?? "";
        $holiday_option_id                  = $post->holiday_option_id                  ?? "";
        $maternal_plan_option_id            = $post->maternal_plan_option_id            ?? "";
        $internment_ambulatory_option_id    = $post->internment_ambulatory_option_id    ?? "";
        $unit_price                         = $post->unit_price                         ?? "";
        $affiliate_id                       = $post->affiliate_id                       ?? "";
        $benefit_date                       = $post->benefit_date                       ?? "";
        $affiliate_number                   = $post->affiliate_number                   ?? "";
        $affiliate_name                     = $post->affiliate_name                     ?? "";
        $bill_number                        = $post->bill_number                        ?? "";
        $modify_coverage                    = $post->modify_coverage                    ?? "";
        $new_honorary                       = $post->new_honorary                       ?? "";
        $new_expenses                       = $post->new_expenses                       ?? "";
        $id                                 = (int) $this->get('id');


        //Validate if any obligatory field is missing
        if(empty($medical_insurance_id))                return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($plan_id))                             return $this->response(['error'=>'No se ha ingresado plan'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($quantity) && $quantity !== '0')       return $this->response(['error'=>'No se ha ingresado cantidad'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($billing_code_id))                     return $this->response(['error'=>'No se ha ingresado código de facturación'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($multiple_operation_value))            return $this->response(['error'=>'No se ha ingresado porcentaje de operación múltiple'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($internment_ambulatory_option_id))     return $this->response(['error'=>'No se han ingresado datos de prestación con respecto a internación o ambulatoria'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($id))                                  return $this->response(['error'=>'No se ha informado el ID de la prestación a actualizar'], REST_Controller::HTTP_BAD_REQUEST);

        //Validate optional fields (if sent)
        if(!empty($benefit_date)){
            if(!$this->validator->validateDate($benefit_date))      return $this->response(['error'=>'Fecha de prestación invalida'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($affiliate_number)){
            if(empty($affiliate_name))      return $this->response(['error'=>'Se debe informar tanto el nombre del afiliado como su número'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($affiliate_name)){
            if(empty($affiliate_number))    return $this->response(['error'=>'Se debe informar tanto el número del afiliado como su nombre'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if($modify_coverage == 1){
            if(empty($new_honorary) && $new_honorary !== '0')    return $this->response(['error'=>'Si se redefinen los porcentajes de cobertura, la nueva cobertura de honorarios no puede ser vacía'], REST_Controller::HTTP_BAD_REQUEST);
            if(empty($new_expenses) && $new_expenses !== '0')    return $this->response(['error'=>'Si se redefinen los porcentajes de cobertura, la nueva cobertura de gastos no puede ser vacía'], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($bill_number)){
            if($unit_price <= 0)            return $this->response(['error'=>'Si se ingreso un numero de factura, el precio unitario debe ser mayor a 0'], REST_Controller::HTTP_BAD_REQUEST);
            switch ($billing_code_id) { //1-Just honoraries - 2-Just expenses - 3-Both
                case 1:
                    $value_honorary = $unit_price;
                    $value_expenses = 0;
                    break;
                case 2:
                    $value_honorary = 0;
                    $value_expenses = $unit_price;
                    break;
                case 3:
                    $value_honorary = $unit_price;
                    $value_expenses = $unit_price;
                    break;
                default:
                    return $this->response(['error'=>'El código de facturación ingresado no existe'], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        //Validate additional field (depending on nomenclator)
        $nomenclator = $this->nomenclator->getNomenclatorById($id);
        if($nomenclator->surgery == 1 && empty($additional)) return $this->response(['error'=>'Se debe seleccionar obligatoriamente un elemento del campo cirugía debido al nomenclador seleccionado'], REST_Controller::HTTP_BAD_REQUEST);

        ////Create the affiliate if informed and if it does not exist
        $affiliateOperation = ["status" => "", "affiliate_id" => $affiliate_id];

        //If an affiliate ID was informed, check if it is valid
        if(!empty($affiliate_id)){
            $affiliate_number = "";     //You won't need this if you have an ID
            $affiliate_name   = "";     //You won't need this if you have an ID
            if (!$this->affiliate->validateID($affiliate_id)){
                return $this->response(['error' => 'El afiliado ingresado no existe en base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        //Check if an affiliate number was informed
        if (!empty($affiliate_number)) {
            //If a number was informed, check it's existence.
            if (!$this->affiliate->checkExistence($affiliate_number)) {
                $affiliateOperation = $this->affiliate->save($medical_insurance_id, $plan_id, $affiliate_number, $affiliate_name);
                if (!$affiliateOperation["status"]) {
                    return $this->response(['error' => 'Error de base de datos'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        //If everything is valid, update the benefit
        $result = $this->benefit->update($remesa, $additional, $quantity, $billing_code_id, $multiple_operation_value, $holiday_option_id, $maternal_plan_option_id, $internment_ambulatory_option_id, $unit_price, $benefit_date, $affiliateOperation["affiliate_id"], $bill_number, $modify_coverage, $new_honorary, $new_expenses,$value_honorary, $value_expenses, $id, $this->token_valid->user_id);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

    //Delete benefit
    public function benefits_delete(){

        $id = (int) $this->get('id');

        if (empty($id)) return $this->response(['error'=>'No se ha informado el ID de la prestación a eliminar'], REST_Controller::HTTP_BAD_REQUEST);

        $result = $this->benefit->delete($id, $this->token_valid->user_id);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

}
