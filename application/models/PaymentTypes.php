<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class PaymentTypes extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    //Get Payment Types
    public function getCategoryCircle(){

        $result = array();

        $query = $this->db->get('payment_type');

        foreach ($query->result_array('payment') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}