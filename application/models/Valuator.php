<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Valuator extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->model('SpecialConditions');
        $this->load->model('Fee');
        $this->load->model('Coverages');
        $this->load->model('Professionals');
    }

    public function valueBenefit($valueBenefitID){

        ////Get the benefit from the table and it's nomenclator
        $this->db->select('B.*,BC.value as billing_code_value,HO.value as holiday_value, MPO.value as maternal_plan_value, IAO.value as internment_ambulatory_value,N.unity as unit');
        $this->db->from('benefits B');
        $this->db->join('nomenclators N',                    'B.nomenclator_id = N.nomenclator_id');
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

        ////Get the benefit's nomenclator
        $this->db->select('N.*');
        $this->db->from('nomenclators N');
        $this->db->where('N.nomenclator_id',$valueBenefit->nomenclator_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'La prestación no posee un nomenclador existente'];

        $nomenclator = $query->row();

        ////Get the benefit's special condition (it may not have one)
        $this->db->select('SC.*,');
        $this->db->from('special_conditions SC');
        $this->db->where('SC.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $this->db->where('SC.plan_id',$valueBenefit->plan_id);
        $this->db->where('SC.provision',$valueBenefit->nomenclator_id);
        //TODO: $this->db->where('SP.benefit_id',$valueBenefitID);  El periodo del benefit tiene que estar entre en periodo de la cond esp
        $query = $this->db->get();

        if (!$query) return ['status' => 'error', 'msg' => 'Error inesperado'];

        if($query->num_rows() !== 0) {
            $specialConditionID = $query->row()->id_special_conditions;
            $specialCondition = $this->SpecialConditions->get_specialconditions_by_id($specialConditionID);
        }

        ////CASE 1)
        //Check if the benefit has any special conditions. If so, value it using the special condition
        if (!empty($specialCondition)) {

            $specialConditionID = $query->row()->id_special_conditions;
            $specialCondition = $this->SpecialConditions->get_specialconditions_by_id($specialConditionID);
            $valueArray = $this->valueBenefitBySpecialCondition($valueBenefit, $specialCondition);
            if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
            return $this->saveValorizedBenefit($valueBenefit,$valueArray['msg']);

        ////CASE 2)
        //Check if the benefit's unit price is different from 0. If so, use it to calculate the honorary and expenses and apply coverage
        } elseif($valueBenefit->unit_price != 0.00){

            $honorary_calculated_value = ($valueBenefit->unit_price)*$valueBenefit->quantity;
            $expenses_calculated_value = ($valueBenefit->unit_price)*$valueBenefit->quantity;
            $valueArray = $this->applyCoverage($honorary_calculated_value,$expenses_calculated_value,$valueBenefit);
            if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
            return $this->saveValorizedBenefit($valueBenefit,$valueArray['msg']);

        ////CASE 3)
        //Default case
        } else {

            //Obtain the fee needed
            $data = $this->getBenefitFee($valueBenefit);
            if ($data['status'] == 'error') return ['status' => 'error', 'msg' => $data['msg']];

            $fee = $data['msg']['fee'];
            $unit = reset($data['msg']['unit']); //Sanitize unit array

            //Obtain the honorary units
            $UH = $this->getUnitHonorary($nomenclator, $valueBenefit);

            ////Calculate expenses
            if ($unit['movement'] == 'F') {
                $expenses_calculated_value = $unit['expenses'] * $valueBenefit->quantity;
            } else {
                $expenses_calculated_value = ($unit['expenses'] * $nomenclator->spending_unity) * $valueBenefit->quantity;
            }

            ////Calculate honoraries

            //Obtain the professional
            $professional = $this->Professionals->getProfessionalsById($valueBenefit->id_professional_data)[0];  //There is only one professional, but the method returns array of arrays so I need the first one

            if ($fee['fee_type_id'] == 1) { //Arancel CMZ

                //Obtain the wanted honorary (the one which medical_career_id is the same as the professional medical_career_id
                $wantedHonorary = array_filter($unit['honoraries'], array(new FilterHonoraryArray($professional['id_medical_career'], $professional['id_category_femeba']), 'equalsMedicalCareer'));
                $honorary = reset($wantedHonorary); //Sanitize

                if ($honorary['movement'] == 'F') {
                    $honorary_calculated_value = $honorary['value'] * $valueBenefit->quantity;
                } else {
                    $honorary_calculated_value = ($honorary['value'] * $UH) * $valueBenefit->quantity;
                }

            } else { //Arancel FEMEBA

                //Obtain the wanted honorary (the one which category_femeba_id is the same as the professional category_femeba_id
                $wantedHonorary = array_filter($unit['honoraries'], array(new FilterHonoraryArray($professional['id_medical_career'], $professional['id_category_femeba']), 'equalsCategoryFemeba'));
                $honorary = reset($wantedHonorary); //Sanitize

                if ($honorary['movement'] == 'F') {
                    $honorary_calculated_value = $honorary['value'] * $valueBenefit->quantity;
                } else {
                    $honorary_calculated_value = ($honorary['value'] * $UH) * $valueBenefit->quantity;
                }

            }

            $valueArray = $this->applyCoverage($honorary_calculated_value, $expenses_calculated_value, $valueBenefit);
            if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
            return $this->saveValorizedBenefit($valueBenefit, $valueArray['msg']);
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

        //Obtain the honorary units
        $UH = $this->getUnitHonorary($nomenclator,$valueBenefit);

        //Calculate honoraries and units depending on the special condition group of values

        if($specialCondition['group_of_values'] == 1){ //1 = Especial

            if ($valueBenefit->internment_ambulatory_value == 0){ //0 = Ambulatorio

                if($specialCondition['type_of_values'] == 1){ // type of value 1 = $

                    $honorary_calculated_value = ($specialCondition['especiales'][0]['honorary']/100) * $valueBenefit->quantity;
                    $expenses_calculated_value = ($specialCondition['especiales'][0]['expenses']/100) * $valueBenefit->quantity;
                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                } else { //type of value 0 = %

                    $honorary_calculated_value = (($specialCondition['especiales'][0]['honorary']/100)*$UH) * $valueBenefit->quantity;
                    $expenses_calculated_value = (($specialCondition['especiales'][0]['expenses']/100)*$nomenclator->spending_unity) * $valueBenefit->quantity;
                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                }

            } else {

                if($specialCondition['type_of_values'] == 1){ // type of value 1 = $

                    $honorary_calculated_value = ($specialCondition['especiales'][1]['honorary']/100) * $valueBenefit->quantity;
                    $expenses_calculated_value = ($specialCondition['especiales'][1]['expenses']/100) * $valueBenefit->quantity;
                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                } else { //type of value 0 = %

                    $honorary_calculated_value = (($specialCondition['especiales'][1]['honorary']/100)*$UH) * $valueBenefit->quantity;
                    $expenses_calculated_value = (($specialCondition['especiales'][1]['expenses']/100)*$nomenclator->spending_unity) * $valueBenefit->quantity;
                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                }

            }

        } else { //0 = unidad

            //Obtain the fee needed
            $data = $this->getBenefitFee($valueBenefit);
            if($data['status'] == 'error') return ['status'=>'error','msg' => $data['msg']];

            $fee    = $data['msg']['fee'];
            $unit   = reset($data['msg']['unit']);  //Sanitize unit array

            //TODO: espear a que defina la parte de aranceles
            return ['status'=>'ok','msg' =>['honoraryValue' => 0, 'expensesValue' => 0]];



        }

        return 0;

    }

    function applyCoverage($honorary_value,$expenses_value,$valueBenefit){
        
        //Obtain the coverage of the benefit
        $this->db->select('C.*');
        $this->db->from('coverages C');
        $this->db->where('C.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $this->db->where('C.plan_id',$valueBenefit->plan_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró una cobertura para la prestación a valorizar'];

        $coverage = $this->Coverages->getCoveragesById($query->row()->id_coverage);

        //Obtain the wanted unit coverage. The wanted unit coverage should have the benefit nomenclator's unit and it should be the same benefit internment_ambulatory's type
        $wantedUnitCoverage = array_filter($coverage['data'], array(new FilterUnitCoverageArray($valueBenefit->unit,$valueBenefit->internment_ambulatory_value), 'containsUnitAndType'));
        $unitCoverage = reset($wantedUnitCoverage);

        //Obtain the benefit's medical insurance
        $this->db->select('MI.*');
        $this->db->from('medical_insurance MI');
        $this->db->where('MI.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró la obra social que tiene asignada la prestación'];

        $medicalInsurance = $query->row();

        //1)Check if the benefit's medical insurance has maternal plan
        if($medicalInsurance->maternal_plan) {

            $coveredHonorary = $honorary_value;
            $coveredExpenses = $expenses_value;

        //2)Check if the benefit modifies coverage
        }elseif ($valueBenefit->modify_coverage){

            $coveredHonorary = $honorary_value * ($valueBenefit->new_honorary / 100);
            $coveredExpenses = $expenses_value * ($valueBenefit->new_expenses / 100);


        //3)Default case: apply benefit's coverage values
        }else{

            $coveredHonorary = $honorary_value * ($unitCoverage['honorary'] / 100);
            $coveredExpenses = $expenses_value * ($unitCoverage['expense'] / 100);

        }

        //4)Check for nocturne/holiday value to add additional value
        if($valueBenefit->holiday_value == 1){
            $additional = ($medicalInsurance->cobertura_fer_noct - 100)/100;
            $coveredHonorary = $coveredHonorary + ($coveredHonorary*$additional);
            $coveredExpenses = $coveredExpenses + ($coveredExpenses*$additional);
        }

        return ['status' => 'ok', 'msg' => ['honoraryValue' => $coveredHonorary, 'expensesValue' => $coveredExpenses]];

    }

    function saveValorizedBenefit($valueBenefit,$valueArray){

        $data = array(
            'value_unit'     => $valueBenefit->unit,
            'value_honorary' => $valueArray['honoraryValue'],
            'value_expenses' => $valueArray['expensesValue'],
            'state'          => 2,

        );

        $this->db->where('benefit_id', $valueBenefit->benefit_id);
        $this->db->update('benefits', $data);

        if ($this->db->affected_rows() == 0) return ['status'=>'error','msg' =>'No se pudo actualizar la prestación con sus valores de honorarios y gastos'];;

        return ['status'=>'ok','msg' =>'Prestación valorizada correctamente'];

    }

    function getUnitHonorary($nomenclator,$valueBenefit){
        if($valueBenefit->billing_code_value == 1){
            return $nomenclator->speciality_unity;
        }elseif ($valueBenefit->billing_code_value == 2){
            return $nomenclator->help_unity;
        }else{
            return $nomenclator->anesthetist_unity;
        }
    }

    function getBenefitFee($valueBenefit){

        //Obtain the fee_type of the benefit fee using the medical_insurance_id
        $this->db->select('MI.femeba');
        $this->db->from('medical_insurance MI');
        $this->db->where('Mi.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'La prestación no posee una obra social existente'];

        $benefit_fee_type = ($query->row()->femeba == 1)? 2 : 1;  //If femeba = 1 (true), fee type is 2 because Arancel-Femeba has ID 2 in fee_types table

        //Obtain the fee of the benefit. Then, obtain the unit based on the benefit nomenclator's unit
        $this->db->select('F.fee_id');
        $this->db->from('fees F');
        $this->db->where('F.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $this->db->where('F.plan_id',$valueBenefit->plan_id);
        $this->db->where('F.fee_type_id',$benefit_fee_type);
        $this->db->where('period_since <=', $valueBenefit->period);
        $this->db->group_start();
        $this->db->where('period_until >=', $valueBenefit->period);
        $this->db->or_where('period_until', null);
        $this->db->group_end();
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró un arancel para la prestación a valorizar'];

        $feeID = $query->row()->fee_id;
        $fee = $this->Fee->getFeeById($feeID);

        //Obtain the wanted unit values. The wanted unit is the benefit nomenclator's unit
        $wantedUnit = array_filter($fee['units'], array(new FilterUnitArray($valueBenefit->unit), 'containsUnit'));

        return ['status' => 'ok', 'msg' => ['fee' => $fee, 'unit' => $wantedUnit]];

    }


}

class FilterUnitArray {
    private $unit;

    function __construct($unit) {
        $this->unit = $unit;
    }

    function containsUnit($unitElement) {
        return $unitElement['unit'] == $this->unit;
    }
}

class FilterUnitCoverageArray {
    private $unit;
    private $type;

    function __construct($unit,$type) {
        $this->unit = $unit;
        $this->type = ($type == 1)? "Internación" :"Ambulatorio";
    }

    function containsUnitAndType($unitElement) {
        return $unitElement['unit'] == $this->unit && $unitElement['type_unit'] == $this->type;
    }
}

class FilterHonoraryArray {
    private $medical_career_id;
    private $category_femeba_id;

    function __construct($medical_career,$category_femeba) {
        $this->medical_career_id = $medical_career;
        $this->category_femeba_id = $category_femeba;
    }

    function equalsMedicalCareer($honoraryElement) {
        return $honoraryElement['id_medical_career'] == $this->medical_career_id ;
    }

    function equalsCategoryFemeba($honoraryElement) {
        return $honoraryElement['id_category_femeba'] == $this->category_femeba_id ;
    }
}
