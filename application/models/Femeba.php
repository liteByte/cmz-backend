<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Femeba extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    //Category Femeba
    public function getFemeba(){

        $result = array();

        $query = $this->db->get('category_femeba');

        foreach ($query->result_array('Femeba') as $row){
            array_push($result,$row);
        }

        return $result;
    }
    
}