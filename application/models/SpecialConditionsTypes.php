<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class SpecialConditionsTypes extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function gettypes(){
        $result = array();

        $this->db->order_by("id_type", "asc");
        $query = $this->db->get('special_conditions_type');

        foreach ($query->result_array('Speciality') as $row) {
            array_push($result, $row);
        }
        return $result;
    }
}