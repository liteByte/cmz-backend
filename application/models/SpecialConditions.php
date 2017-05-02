<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class SpecialConditions extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    public function save_special($medical_insurance_id, $planArray, $provision, $type, $period_since, $type_of_values, $group_of_values,  $especiales){

        foreach($planArray as $plan_id) {

            //Get current special condition and validate if it's date is newer than this one. If so, close the old one
            $currentSpecialCondition = $this->SpecialConditions->getCurrentSpecialConditionByKey($medical_insurance_id, $plan_id, $provision)[0];
            if (!empty($currentSpecialCondition)) {

                if (date($period_since) > date($currentSpecialCondition['period_since'])) {

                    $close_period_date = date('Y-m-d', (strtotime('-1 month', strtotime($period_since))));
                    $data = ['period_until' => $close_period_date];

                    //Update old special condition by closing it's period_until
                    $this->db->where('id_special_conditions', $currentSpecialCondition['id_special_conditions']);
                    $this->db->update('special_conditions', $data);

                } else {

                    return "Ya existe una condición especial para la Obra Social, Plan y Nomenclador seleccionado en el período ingresado";

                }
            }

            $data = [
                "medical_insurance_id"  => $medical_insurance_id,
                "plan_id"               => $plan_id,
                "provision"             => $provision,
                "type"                  => $type,
                "period_since"          => $period_since,
                "type_of_values"        => $type_of_values,
                "group_of_values"       => $group_of_values
            ];

            $result = $this->db->insert('special_conditions', $data);
            $errors = $this->db->error();

            if ($errors['code'] == 1062)    return "Error: Datos duplicados, en la base de datos";
            if (!$result)                   return "Error al intentar crear nueva Cobertura";


            //Obtain last inserted user id
            $id_special_conditions = $this->db->insert_id();

            foreach ($especiales as $esp) {
                $new_row = [
                    "type_unit"             => $esp->type_unit,
                    "honorary"              => $esp->honorary,
                    "expenses"              => $esp->expenses,
                    "id_special_conditions" => $id_special_conditions,
                ];
                $result = $this->db->insert('special_conditions_details', $new_row);

                $errors = $this->db->error();

                if ($errors['code'] == 1062) return "Error: Datos duplicados, en la base de datos";
                if (!$result) return "Error al intentar crear nueva Cobertura";
            }
        }

        return  true;
    }

    public function save_unit($medical_insurance_id, $planArray, $provision, $type, $period_since, $type_of_values, $group_of_values, $unit, $quantity_units){

        foreach($planArray as $plan_id) {

            //Get current special condition and validate if it's date is newer than this one. If so, close the old one
            $currentSpecialCondition = $this->SpecialConditions->getCurrentSpecialConditionByKey($medical_insurance_id, $plan_id, $provision)[0];
            if (!empty($currentSpecialCondition)) {

                if (date($period_since) > date($currentSpecialCondition['period_since'])) {

                    $close_period_date = date('Y-m-d', (strtotime('-1 month', strtotime($period_since))));
                    $data = ['period_until' => $close_period_date];

                    //Update old special condition by closing it's period_until
                    $this->db->where('id_special_conditions', $currentSpecialCondition['id_special_conditions']);
                    $this->db->update('special_conditions', $data);

                } else {

                    return "Ya existe una condición especial para la Obra Social, Plan y Nomenclador seleccionado en el período ingresado";

                }
            }

            $data = [
                "medical_insurance_id"  => $medical_insurance_id,
                "plan_id"               => $plan_id,
                "provision"             => $provision,
                "type"                  => $type,
                "period_since"          => $period_since,
                "type_of_values"        => $type_of_values,
                "group_of_values"       => $group_of_values
            ];

            $result = $this->db->insert('special_conditions', $data);
            $errors = $this->db->error();

            if ($errors['code'] == 1062) return "Error: Datos duplicadoss, en la base de datos";
            if (!$result) return "Error al intentar crear nueva Cobertura";

            //Obtain last inserted user id
            $id_special_conditions = $this->db->insert_id();

            $new_row = [
                "unit"                  => $unit,
                "quantity_units"        => $quantity_units,
                "id_special_conditions" => $id_special_conditions,
            ];
            $result = $this->db->insert('special_conditions_details', $new_row);
            $errors = $this->db->error();

            if ($errors['code'] == 1062) return "Error: Datos duplicadoss, en la base de datos";
            if (!$result) return "Error al intentar crear nueva Cobertura";
        }

        return  true;
    }

    public function  update_special($medical_insurance_id, $plan_id, $provision, $type, $period_since, $type_of_values, $group_of_values, $especiales, $id ){
        $data = [
            "medical_insurance_id"  => $medical_insurance_id,
            "plan_id"               => $plan_id,
            "provision"             => $provision,
            "type"                  => $type,
            "period_since"          => $period_since ,
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

    public function  update_unit($medical_insurance_id, $plan_id, $provision, $type, $period_since, $type_of_values, $group_of_values,$unit, $quantity_units, $id ){
        $data = [
            "medical_insurance_id"  => $medical_insurance_id,
            "plan_id"               => $plan_id,
            "provision"             => $provision,
            "type"                  => $type,
            "period_since"          => $period_since ,
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
        $this->db->select('plans.description as description_plan');
        $this->db->select('nomenclators.type as nomenclator_type,  nomenclators.code, nomenclators.class, nomenclators.description');
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
        $this->db->select('nomenclators.type as nomenclator_type, nomenclators.code, nomenclators.class, nomenclators.description');
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
                $empty_array  = [
                    ["type_unit" => "Ambulatorio", "honorary"  => 0, "expenses"  => 0],
                    ["type_unit" => "Internación", "honorary"  => 0, "expenses"  => 0],
                ];

                foreach ($query_unit->result_array('special_conditions') as $qs) {
                    $row = array_merge($row, $qs);
                    $row['especiales'] = $empty_array;
                }
            }
//            array_push($result,$row);
            $result = $row;
        }
        return $result;
    }

    public function delete_specialconditions($id){

        $query = $this->db->get_where('special_conditions', ["id_special_conditions" => $id]);

        if ($query->num_rows()) {

            $this->db->where('id_special_conditions', $id);
            $result = $this->db->delete('special_conditions_details');
            if (!$result) return "Error al intentar eliminar condicion";

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

    function getCurrentSpecialConditionByKey($medical_insurance_id, $plan_id, $nomenclator_id){

        $this->db->select('SC.*');
        $this->db->from ('special_conditions SC');
        $this->db->where('SC.medical_insurance_id',$medical_insurance_id);
        $this->db->where('SC.provision',$nomenclator_id);
        $this->db->where('SC.plan_id',$plan_id);
        $this->db->where('SC.period_until',null);
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];
        return $query->result_array();

    }
}
