<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class CreditDebitNoteController extends AuthController{

    private $token_valid;
    protected $access = "ABMdebitocredito";
    function __construct(){
        parent::__construct();
        $this->load->model('CreditDebitNote');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create credit or debit note
    public function creditDebitNote_post(){

        $post = json_decode(file_get_contents('php://input'));

        $medical_insurance_id = $post->medical_insurance_id ?? "";
        $id_bill              = $post->id_bill              ?? "";
        $document_type        = $post->document_type        ?? "";
        $branch_office        = $post->branch_office        ?? "";
        $form_type            = $post->form_type            ?? "";

        //Validate if any obligatory field is missing
        if(empty($medical_insurance_id))    return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($id_bill))                 return $this->response(['error'=>'No se ha ingresado factura'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($document_type))           return $this->response(['error'=>'No se han ingresado el tipo (crédito/débito)'], REST_Controller::HTTP_BAD_REQUEST);

        //If everything is valid, save the credit/debit
        $result = $this->CreditDebit->createNote($medical_insurance_id, $id_bill, $document_type,$branch_office,$form_type);
        if ($result['status'] == 'error'){
            return $this->response(['error'=>$result['msg']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg'=>$result['msg']], REST_Controller::HTTP_OK);
        }

    }

    //
    public function creditDebit_put(){


    }

    //
    public function creditDebit_delete(){


    }













}
