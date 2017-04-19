<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class HolidayOptionController extends AuthController{

    private $token_valid;
    protected $access = "*";

    function __construct(){
        parent::__construct();
        $this->load->model('HolidayOption');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function holiday_option_get(){
        $options = $this->HolidayOption->getHolidayOptions();
        return $this->response($options, RC::HTTP_OK);
    }

}