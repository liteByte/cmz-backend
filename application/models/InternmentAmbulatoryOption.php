<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class InternmentAmbulatoryOption extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    public function getInternmentAmbulatoryOptions(){

        $result = array();

        $query = $this->db->get('internment_ambulatory_options');

        foreach ($query->result_array('InternmentAmbulatoryOption') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}