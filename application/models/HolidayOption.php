<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class HolidayOption extends CI_Model{

    public function __construct(){
        parent::__construct();
    }


    public function getHolidayOptions(){

        $result = array();

        $query = $this->db->get('holiday_options');

        foreach ($query->result_array('HolidayOption') as $row){
            array_push($result,$row);
        }

        return $result;
    }

}