<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class BillingCode extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    public function getBillingCodes(){

        $result = array();

        $query = $this->db->get('billing_codes');

        foreach ($query->result_array('BillingCode') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}