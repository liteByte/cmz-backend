<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Coverages extends CI_Model{

    public function __construct(){
        parent::__construct();
    }
    

    public function save($plan_id, $medical_insurance_id,$data ){

        $new_coverage = [
            "plan_id"               =>$plan_id,
            "medical_insurance_id"  =>$medical_insurance_id,
            "status"                => 1,
        ];

        $result = $this->db->insert('coverages', $new_coverage);

        if(!$result){ $result = "Error al intentar crear nueva Cobertura"; return $result; }

        //Obtain last inserted user id
        $id_coverage = $this->db->insert_id();

        foreach ($data as $new_units_coverage){
            $new_row = [
                "id_coverage"   => $id_coverage,
                "type_unit"     => $new_units_coverage->type_units,
                "unit"          => $new_units_coverage->units,
                "honorary"      => $new_units_coverage->honorary,
                "expenses"      => $new_units_coverage->expense,
            ];
            $result = $this->db->insert('units_coverage', $new_row);
        }

        if(!$result){ $result = "Error al intentar crear nueva Cobertura"; return $result; }
        return "OK";
    }

    public function getCoverages(){
        $result = array();

        $this->db->select('coverages.id_coverage , coverages.plan_id , coverages.plan_id , units_coverage.*, medical_insurance.medical_insurance_id,medical_insurance.denomination , plans.plan_id, plans.description ');
        $this->db->from('coverages');
        $this->db->join('units_coverage', 'units_coverage.id_coverage = coverages.id_coverage');
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = coverages.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = coverages.plan_id');
        $this->db->order_by("medical_insurance.settlement_name", "asc");
        $this->db->where('coverages.status', 1);
        $query =  $this->db->get();
        
        if(!$query) return false;  
        
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

    public function update($id, $plan_id,  $medical_insurance_id, $id_units_coverage, $data ){

        $update_coverage = [
            "plan_id"               =>$plan_id,
            "medical_insurance_id"  =>$medical_insurance_id
        ];

        $this->db->where('id_coverage', $id);
        $result = $this->db->update('coverages', $update_coverage);

        if(!$result){ return "Error al intentar actualizar datos";}

        //Update table "units_coverage"
        foreach ($data as $update_units_coverage){
            $update_row = [
                "unit"                  => $update_units_coverage->units,
                "type_unit"             => $update_units_coverage->type_units,
                "honorary"              => $update_units_coverage->honorary,
                "expenses"              => $update_units_coverage->expense
            ];
            $this->db->where('id_units_coverage', $id_units_coverage);
            $this->db->update('units_coverage', $update_row);
        }

        $afftectedRows = $this->db->affected_rows();

        if(!$afftectedRows){
            return  "No se actualizaron registros";;
        }
        return true;
    }
}