<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class MaternalPlanOption extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    public function getMaternalPlanOptions(){

        $result = array();

        $query = $this->db->get('maternal_plan_options');

        foreach ($query->result_array('MaternalPlanOption') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}