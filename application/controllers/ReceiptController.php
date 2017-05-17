<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class ReceiptController extends AuthController{

    private $token_valid;
    protected $access = "Facturacion";

    function __construct(){
        parent::__construct();
        $this->load->library('Response_msg');
        $this->load->library('pdf');
        $this->load->model('receipt');
        $this->token_valid = $this->validateToken();
    }


    public function receiptPrint_get(){

        $id = $this->get('id');

        if(empty($id)) return $this->response(['error' => 'No se ha informado el ID de la factura asociada al remito que se desea imprimir'], RC::HTTP_BAD_REQUEST);

        $result = $this->receipt->generateReceipt($id);

        if($result['status'] == 'error') return $this->response(['error'=>$result['msg']], RC::HTTP_INTERNAL_SERVER_ERROR);

        $html = $this->load->view('documents/receipt.html',$result['msg'],TRUE);

        return $this->pdf->pdf_create2($html);
        
    }
}

