<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class SpecialConditionsController extends AuthController{

    function __construct(){
        parent::__construct();
    }

    public function specialconditions_post(){

    }

}

