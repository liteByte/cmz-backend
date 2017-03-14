<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class CategoryCircle extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    //Category Circle
    public function getCategoryCircle(){

        $result = array();

        $query = $this->db->get('category_circle');

        foreach ($query->result_array('category') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}