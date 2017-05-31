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
        $this->load->model('CreditDebit');
        $this->load->model('CreditDebitNote');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //
    public function creditDebit_post(){


    }

    //
    public function creditDebit_put(){


    }

    //
    public function creditDebit_delete(){


    }













}
