<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class PaymentTypes extends CI_Model{

    public function __construct(){
        parent::__construct();
    }
    
    public function getPaymentTypes(){

        $result = array();

        $query = $this->db->get('payment_type');

        foreach ($query->result_array('payment') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}