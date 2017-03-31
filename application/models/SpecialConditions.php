<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class SpecialConditions extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    public function save_special($medical_insurance_id, $plan_id, $nomenclator_type, $provision, $type, $period_of_validity, $type_of_values, $group_of_values,  $especiales){

        $data = [
            "medical_insurance_id"  => $medical_insurance_id,
            "plan_id"               => $plan_id,
            "nomenclator_type"      => $nomenclator_type,
            "provision"             => $provision,
            "type"                  => $type,
            "period_of_validity"    => $period_of_validity ,
            "type_of_values"        => $type_of_values,
            "group_of_values"       => $group_of_values
        ];

        $result = $this->db->insert('special_conditions', $data);
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";


        //Obtain last inserted user id
        $id_special_conditions = $this->db->insert_id();

        foreach ($especiales as $esp){
            $new_row = [
                "type_unit"             => $esp->type_unit,
                "honorary"              => $esp->honorary,
                "expenses"              => $esp->expenses,
                "id_special_conditions" => $id_special_conditions,
            ];
            $result = $this->db->insert('special_conditions_details', $new_row);

            $errors = $this->db->error();
            if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
            if(!$result)                return "Error al intentar crear nueva Cobertura";
        }
        return  true;
    }

    public function save_unit($medical_insurance_id, $plan_id, $nomenclator_type, $provision, $type, $period_of_validity, $type_of_values, $group_of_values, $unit, $quantity_units){
        $data = [
            "medical_insurance_id"  => $medical_insurance_id,
            "plan_id"               => $plan_id,
            "nomenclator_type"      => $nomenclator_type,
            "provision"             => $provision,
            "type"                  => $type,
            "period_of_validity"    => $period_of_validity ,
            "type_of_values"        => $type_of_values,
            "group_of_values"       => $group_of_values
        ];

        $result = $this->db->insert('special_conditions', $data);
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";

        //Obtain last inserted user id
        $id_special_conditions = $this->db->insert_id();

        $new_row =[
            "unit"              => $unit,
            "quantity_units"    => $quantity_units,
            "id_special_conditions" => $id_special_conditions,
        ];
        $result = $this->db->insert('special_conditions_details', $new_row);
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";
        return  true;
    }

    public function  update_special($medical_insurance_id, $plan_id, $nomenclator_type, $provision, $type, $period_of_validity, $type_of_values, $group_of_values, $especiales, $id ){
        $data = [
            "medical_insurance_id"  => $medical_insurance_id,
            "plan_id"               => $plan_id,
            "nomenclator_type"      => $nomenclator_type,
            "provision"             => $provision,
            "type"                  => $type,
            "period_of_validity"    => $period_of_validity ,
            "type_of_values"        => $type_of_values,
            "group_of_values"       => $group_of_values
        ];

        $this->db->where('id_special_conditions', $id);
        $result = $this->db->update('special_conditions', $data);
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";

        foreach ($especiales as $esp){
            $new_row = [
                "type_unit"             => $esp->type_unit,
                "honorary"              => $esp->honorary,
                "expenses"              => $esp->expenses,
            ];

            $this->db->where('id_special_conditions', $id);
            $this->db->where('type_unit', $esp->type_unit);
            $result = $this->db->update('special_conditions_details', $new_row);
            $errors = $this->db->error();
            if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
            if(!$result)                return "Error al intentar crear nueva Cobertura";
        }

        return 1;
    }

    public function  update_unit($medical_insurance_id, $plan_id, $nomenclator_type, $provision, $type, $period_of_validity, $type_of_values, $group_of_values,$unit, $quantity_units, $id ){
        $data = [
            "medical_insurance_id"  => $medical_insurance_id,
            "plan_id"               => $plan_id,
            "nomenclator_type"      => $nomenclator_type,
            "provision"             => $provision,
            "type"                  => $type,
            "period_of_validity"    => $period_of_validity ,
            "type_of_values"        => $type_of_values,
            "group_of_values"       => $group_of_values
        ];

        $this->db->where('id_special_conditions', $id);
        $result = $this->db->update('special_conditions', $data);
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";

        $new_row =[
            "unit"              => $unit,
            "quantity_units"    => $quantity_units,
        ];

        $this->db->where('id_special_conditions', $id);
        $result = $this->db->update('special_conditions_details', $new_row);
        $errors = $this->db->error();
        if($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
        if(!$result)                return "Error al intentar crear nueva Cobertura";
        return 1;
    }

    public function get_specialconditions(){

        $result = [];

        $this->db->select('special_conditions.*');
        $this->db->select('medical_insurance.denomination');
        $this->db->select('plans.description');
        $this->db->select('nomenclators.code, nomenclators.class, nomenclators.description');
        $this->db->from('special_conditions');
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = special_conditions.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = special_conditions.plan_id');
        $this->db->join('nomenclators', 'nomenclators.nomenclator_id = special_conditions.provision');
        $query =  $this->db->get();

        if(!$query) return false;
        foreach ($query->result_array('special_conditions') as $row){
            if($row['group_of_values']){
                $id =  $row['id_special_conditions'];
                $this->db->select('special_conditions_details.type_unit, special_conditions_details.honorary, special_conditions_details.expenses,');
                $this->db->from('special_conditions_details');
                $this->db->where('id_special_conditions', $id);
                $query_specials =  $this->db->get();

                foreach ($query_specials->result_array('special_conditions') as $qs){
                    $row['especiales']= $query_specials->result_array();
                }
            }else{
                $id =  $row['id_special_conditions'];
                $this->db->select('special_conditions_details.unit, special_conditions_details.quantity_units');
                $this->db->from('special_conditions_details');
                $this->db->where('id_special_conditions', $id);
                $query_unit =  $this->db->get();

                foreach ($query_unit->result_array('special_conditions') as $qs){
                    $row = array_merge($row,$qs);
                }
            }
            array_push($result,$row);
        }
        return $result;
    }

    public function get_specialconditions_by_id($id){
        $result = [];

        $this->db->select('special_conditions.*');
        $this->db->select('medical_insurance.denomination');
        $this->db->select('plans.description');
        $this->db->select('nomenclators.code, nomenclators.class, nomenclators.description');
        $this->db->from('special_conditions');
        $this->db->where('id_special_conditions', $id);
        $this->db->join('medical_insurance', 'medical_insurance.medical_insurance_id = special_conditions.medical_insurance_id');
        $this->db->join('plans', 'plans.plan_id = special_conditions.plan_id');
        $this->db->join('nomenclators', 'nomenclators.nomenclator_id = special_conditions.provision');
        $query = $this->db->get();

        if (!$query) return false;
        foreach ($query->result_array() as $row) {

            if ($row['group_of_values']) {
                $this->db->select('special_conditions_details.type_unit, special_conditions_details.honorary, special_conditions_details.expenses,');
                $this->db->from('special_conditions_details');
                $this->db->where('id_special_conditions', $id);
                $query_specials = $this->db->get();

                foreach ($query_specials->result_array() as $qs) {
                    $row['especiales'] = $query_specials->result_array();
                }
                $row['unit']    =   '';
                $row['quantity_units'] = '';
            } else {
                $this->db->select('special_conditions_details.unit, special_conditions_details.quantity_units');
                $this->db->from('special_conditions_details');
                $this->db->where('id_special_conditions', $id);
                $query_unit = $this->db->get();
                $empty_array [] = [
                    ["type_unit" => "Ambulatorio", "honorary"  => 0, "expenses"  => 0],
                    ["type_unit" => "Internación", "honorary"  => 0, "expenses"  => 0],
                ];

                foreach ($query_unit->result_array('special_conditions') as $qs) {
                    $row = array_merge($row, $qs);
                    $row['especiales'] = $empty_array;
                }
            }
            array_push($result,$row);
        }
        return $result;
    }

    public function delete_specialconditions($id){

        $query = $this->db->get_where('special_conditions', ["id_special_conditions" => $id]);

        if ($query->num_rows()) {
            $this->db->where('id_special_conditions', $id);
            $result = $this->db->delete('special_conditions');
            $errors = $this->db->error();

            if ($errors['code'] == '1451') return "No se puede eliminar la condicion especial, ya que posee información relacionada";
            if (!$result) return "Error al intentar eliminar condicion";
            return true;
        }else{
            return "El Id no existe en la base de datos";
        }
    }
}