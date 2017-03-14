<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class MedicalCareer extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    
    public function getMedicalCareer(){

        $result = array();

        $query = $this->db->get('medical_career');

        foreach ($query->result_array('medical') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}