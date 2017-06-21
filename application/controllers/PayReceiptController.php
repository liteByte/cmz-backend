<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class PayReceiptController extends AuthController{

    private $token_valid;
    protected $access = "Facturacion";

    function __construct(){
        parent::__construct();
        $this->load->library('Response_msg');
        $this->load->library('pdf');
        $this->load->model('PayReceipt');
        $this->token_valid = $this->validateToken();
    }

    //Cancel a receipt
    public function payReceipt_delete(){

        $id = $this->get('id');

        if(empty($id)) return $this->response(['error' => 'No se ha informado el ID del recibo que se desea anular'], RC::HTTP_BAD_REQUEST);

        $result = $this->PayReceipt->cancelPayReceipt($id);

        if($result['status'] == 'error'){
            return $this->response(['error'=> $result['msg']], RC::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            return $this->response(['msg' => $result['msg']], RC::HTTP_OK);
        }


    }

    //Get all receipts of a bill
    public function receiptsByBill_get(){

        $id = $this->get('id');

        if(empty($id)) return $this->response(['error' => 'No se ha informado el ID de la factura cuyos recibos se quiere consultar'], RC::HTTP_BAD_REQUEST);

        $receipts = $this->PayReceipt->getReceipts($id);
        return $this->response($receipts, RC::HTTP_OK);

    }

    public function payReceiptPrint_get(){

        $id = $this->get('id');

        if(empty($id)) return $this->response(['error' => 'No se ha informado el ID del recibo que se desea imprimir'], RC::HTTP_BAD_REQUEST);

        $result = $this->PayReceipt->getPrintData($id);

        if($result['status'] == 'error') return $this->response(['error'=>$result['msg']], RC::HTTP_INTERNAL_SERVER_ERROR);

        $html = $this->load->view('documents/salesReceipt.html',$result['msg'],TRUE);

        return $this->pdf->pdf_create2($html);

    }







}

