<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class BillController extends AuthController{

    private $token_valid;
    protected $access = "Facturacion";

    function __construct(){
        parent::__construct();
        $this->load->library('Response_msg');
        $this->load->model('bill');
        $this->token_valid = $this->validateToken();
    }

    public function bill_post()    {
        $data = json_decode(file_get_contents('php://input'), TRUE);
        
        $this->form_validation->set_data($data);
        if ($this->form_validation->run() == FALSE) {
            $msg = $this->form_validation->error_array();
            $msg = reset($msg);
            return $this->response(['error' => $msg], RC::HTTP_BAD_REQUEST);
        }

        //Set document type to F->Factura
        $data['document_type'] = 'F';

        //Start billing process
        $result = $this->bill->bill_init($data);

        if($result['status'] == "ok"){

            ////Check if medical insurance is O.S.D.E. If so, change document_type to 'L' and bill again
            //O.S.D.E id is 17
            if($data['id_medical_insurance'] == 17){

                $data['document_type'] = 'L';

                //Start billing process again for OSDE, this time with document type L
                $result = $this->bill->bill_init($data);

                if($result['status'] == "ok") {
                    return $this->response(['msg' => $result['msg']], RC::HTTP_OK);
                }else{
                    return $this->response(['error'=>$result['msg']], RC::HTTP_INTERNAL_SERVER_ERROR);
                }

            }

            return $this->response(['msg'=>$result['msg']], RC::HTTP_OK);

        } else {

            return $this->response(['error'=>$result['msg']], RC::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    public function billPrint_get(){

        $result = $this->bill->getPrintData('1');

        if($result['status'] == 'error') return $this->response(['error'=>$result['msg']], RC::HTTP_INTERNAL_SERVER_ERROR);

        print_r($result['msg']);die();
        $this->load->view('documents/factura.html');

    }
}

