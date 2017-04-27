<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Bill extends CI_Model{

    private $table = "bill";
    private $table_d = "bill_details_grouped";

    public function __construct(){
        parent::__construct();
    }


    public function bill_init($data){

        print_r($data);
    }
}