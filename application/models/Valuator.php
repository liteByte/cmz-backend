<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Valuator extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->model('SpecialConditions');
    }

    public function valueBenefit($valueBenefitID){

        ////Get the benefit from the table
        $this->db->select('B.*,BC.value as billing_code_value,HO.value as holiday_value, MPO.value as maternal_plan_value, IAO.value as internment_ambulatory_value');
        $this->db->from('benefits B');
        $this->db->join('billing_codes BC',                  'B.billing_code_id = BC.billing_code_id');
        $this->db->join('holiday_options HO',                'B.holiday_option_id = HO.holiday_option_id');
        $this->db->join('maternal_plan_options MPO',         'B.maternal_plan_option_id = MPO.maternal_plan_option_id');
        $this->db->join('internment_ambulatory_options IAO', 'B.internment_ambulatory_option_id = IAO.internment_ambulatory_option_id');
        $this->db->where('B.benefit_id',$valueBenefitID);
        $this->db->where('B.active','active');
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró la prestación'];

        $valueBenefit = $query->row();

        ////Check if the benefit has any special conditions. If so, value it using the special condition
        $this->db->select('SC.*,');
        $this->db->from('special_conditions SC');
        $this->db->where('SC.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $this->db->where('SC.plan_id',$valueBenefit->plan_id);
        $this->db->where('SC.provision',$valueBenefit->nomenclator_id);
        //TODO: $this->db->where('SP.benefit_id',$valueBenefitID);  El periodo del benefit tiene que estar entre en periodo de la cond esp
        $query = $this->db->get();



        if (!$query) return ['status' => 'error', 'msg' => 'Error inesperado'];

        if ($query->num_rows() !== 0) {
            $specialConditionID = $query->row()->id_special_condition;
            $specialCondition = $this->SpecialConditions->get_specialconditions_by_id($specialConditionID);
            return valueBenefitBySpecialCondition($valueBenefit, $specialCondition);
        }



    }

    function valueBenefitBySpecialCondition($valueBenefit,$specialCondition){

        //Obtain unit value. First, obtain the benefit nomenclator and then choose the appropriate
        //nomenclator unit based on the benefit billing code
        $this->db->select('N.*');
        $this->db->from('nomenclators N');
        $this->db->where('N.nomenclator_id',$valueBenefit->nomenclator_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'La prestación no posee un nomenclador existente'];

        $nomenclator = $query->row();

        if($valueBenefit->billing_code_value == 1){
            $UH = $nomenclator->speciality_unity;
        }elseif ($valueBenefit->billing_code_value == 2){
            $UH = $nomenclator->help_unity;
        }else{
            $UH = $nomenclator->anesthetist_unity;
        }

        //Calculate honoraries and units depending on the special condition group of values

        if($specialCondition['group_of_values'] == 1){ //1 = Especial

            if ($valueBenefit->internment_ambulatory_value == 0){ //0 = Ambulatorio

                if($specialCondition['type_of_values'] == 1){ //1 = $

                    $honorary_calculated_value = $specialCondition['especiales'][0];

                }

            }

        }else{

        }

    }

}
