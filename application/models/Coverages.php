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
        $errors = $this->db->error();

        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";

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
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";
        return "OK";
    }

    public function getCoverages(){
        $result = array();

        $this->db->select('coverages.id_coverage , medical_insurance.medical_insurance_id,medical_insurance.denomination, plans.plan_id,  plans.description , units_coverage.*');
        $this->db->from('coverages');
        $this->db->join('units_coverage', 'units_coverage.id_coverage = coverages.id_coverage');
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = coverages.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = coverages.plan_id');
        $this->db->order_by("medical_insurance.denomination", "asc");
        $this->db->order_by("plans.description", "asc");
        $this->db->order_by("units_coverage.type_unit", "asc");
        $this->db->order_by("units_coverage.unit", "asc");
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

        $this->db->select('coverages.id_coverage , medical_insurance.medical_insurance_id,medical_insurance.denomination, plans.plan_id,  plans.description , units_coverage.*');
        $this->db->from('coverages');
        $this->db->join('units_coverage', 'units_coverage.id_coverage = coverages.id_coverage');
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = coverages.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = coverages.plan_id');
        $this->db->order_by("medical_insurance.denomination", "asc");
        $this->db->order_by("plans.description", "asc");
        $this->db->order_by("units_coverage.type_unit", "asc");
        $this->db->order_by("units_coverage.unit", "asc");
        $this->db->where('coverages.id_coverage', $id);
        $this->db->where('coverages.status', 1);
        $query = $this->db->get();

        if($query !== FALSE && $query->num_rows() > 0){
            $i = 0;
            $data_internacion = [];
            $data_ambulatorio = [];

            foreach ($query->result_array() as $row){


                if($i == 0){
                    $result['id_coverage'] = $row['id_coverage'];
                    $result['medical_insurance_id'] = $row['medical_insurance_id'];
                    $result['denomination'] = $row['denomination'];
                    $result['plan_id'] = $row['plan_id'];
                    $result['description'] = $row['description'];
                    $i++;
                }
                if($row['type_unit'] === "Ambulatorio"){
                    $temp2['id_units_coverage'] = $row['id_units_coverage'];
                    $temp2['unit'] = $row['unit'];
                    $temp2['type_unit'] = $row['type_unit'];
                    $temp2['honorary'] = $row['honorary'];
                    $temp2['expense'] = $row['expenses'];
                    array_push($data_ambulatorio, $temp2);
                }

                if($row['type_unit'] == "Internación"){
                    $temp1['id_units_coverage'] = $row['id_units_coverage'];
                    $temp1['unit'] = $row['unit'];
                    $temp1['type_unit'] = $row['type_unit'];
                    $temp1['honorary'] = $row['honorary'];
                    $temp1['expense'] = $row['expenses'];
                    array_push($data_internacion, $temp1);
                }
            }



            $temp = array_merge($data_ambulatorio ,$data_internacion  );

            $result['data'] = $temp;
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

    public function update($id, $plan_id,  $medical_insurance_id, $data ){

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
                "unit"                  => $update_units_coverage->unit,
                "type_unit"             => $update_units_coverage->type_unit,
                "honorary"              => $update_units_coverage->honorary,
                "expenses"              => $update_units_coverage->expense
            ];

            $this->db->where('id_coverage', $id);
            $this->db->where('unit', $update_units_coverage->unit);
            $this->db->where('type_unit', $update_units_coverage->type_unit);
            $this->db->update('units_coverage', $update_row);
            $errors = $this->db->error();

            if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
            if(!$result)                return "Error al intentar crear nueva Cobertura";
        }

        return true;
    }
}