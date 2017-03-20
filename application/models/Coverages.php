<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Coverages extends CI_Model{

    public function __construct(){
        parent::__construct();
    }
    
    
    public function getCoverages(){
        $result = array();

        $this->db->select('*');
//        $this->db->select('professionals.* , units_coverage.*, medical_insurance.*, plans.* ');
        $this->db->from('coverages');
        $this->db->join('units_coverage', 'units_coverage.id_coverage = coverages.id_coverage');
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = coverages.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = coverages.plan_id');
        $this->db->order_by("medical_insurance.settlement_name", "asc");
        $this->db->where('coverages.status', true);
        $query =  $this->db->get();
        
        if(!$query->row()){ return false;  }
        
        foreach ($query->result_array('Coverages') as $row){
            array_push($result,$row);
        }
        return $result;
    }


    public function getCoveragesById($id)
    {
        $result = [];

        $this->db->select();
        $this->db->from('coverages');
        $this->db->join('units_coverage', 'units_coverage.id_coverage = coverages.id_coverage');
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = coverages.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = coverages.plan_id');
        $this->db->order_by("medical_insurance.settlement_name", "asc");
        $this->db->where('coverages.id_coverage', $id);
        $this->db->where('coverages.status', 1);
        $query = $this->db->get();

        if($query !== FALSE && $query->num_rows() > 0){
            foreach ($query->result_array() as $row) {
                $result[] = $row;
            }
        }else{
            return false;
        }

        return $result;
    }

    public function delete($id, $downUserId){
        $now = date('Y-m-d H:i:s');

        //Delete Coverages

        $this->db->where('coverages.id_coverage', $id);
        $this->db->update('coverages', array('status' => 0, 'date_update' => $now, 'down_user_id' => $downUserId));
        $afftectedRows = $this->db->affected_rows();

        if(!$afftectedRows){
            return false;
        }
        return true;
    }
    
}