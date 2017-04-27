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
        $this->token_valid = $this->validateToken(apache_request_headers());
        $this->load->library('Response_msg');
        $this->load->model('bill');

    }

    public function bill_post()    {
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $this->form_validation->set_data($data);

        if ($this->form_validation->run() == FALSE) {
            $msg = $this->form_validation->error_array();
            $msg = reset($msg);
            return $this->response(['error' => $msg], RC::HTTP_BAD_REQUEST);
            // validation_errors()
        }

        $this->bill->bill_init($data);

    }
}
