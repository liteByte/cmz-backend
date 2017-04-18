<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Affiliate extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    //Creates the affiliate in 'affiliates'
    public function save($medical_insurance_id, $plan_id, $affiliate_number, $affiliate_name){

        $data = array(
            'medical_insurance_id'      => $medical_insurance_id,
            'plan_id'                   => $plan_id,
            'affiliate_number'          => $affiliate_number,
            'affiliate_name'            => $affiliate_name,
            'active'                    => 'active'
        );

        $this->db->insert('affiliates', $data);
        if ($this->db->affected_rows() == 0) return false;

        return true;

    }

    public function getAffiliates(){

        $result = array();

        $query = $this->db->get('affiliates');

        foreach ($query->result_array('Affiliate') as $row){
            $result[] = $row;
        }

        return $result;

    }

    //Checks if an affiliate with a certain number exists
    public function checkExistence($affiliate_number){

        $query = $this->db->get_where('affiliates', array('affiliate_number' => $affiliate_number));
        if ($query->num_rows() > 0) return true;

        return false;

    }

    public function validateData($bank_code){

        //Bank code validation
        $query = $this->db->get_where('banks', array('bank_code' => $bank_code));
        if ($query->num_rows() > 0) return "El código de banco ingresado esta siendo utilizado";

        return "OK";

    }

}
