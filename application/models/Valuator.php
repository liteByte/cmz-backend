<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Valuator extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->model('SpecialConditions');
        $this->load->model('Fee');
        $this->load->model('Coverages');
        $this->load->model('Professionals');
        $this->load->model('MedicalInsurance');
        $this->load->model('Nomenclator');
    }

    public function valueBenefit($valueBenefitID){

        ////Get the benefit from the table and it's nomenclator unit
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

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado al tratar de valorizar la prestación'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró la prestación'];

        $valueBenefit = $query->row();

        ////Get the benefit's nomenclator
        $nomenclator = $this->Nomenclator->getNomenclatorById($valueBenefit->nomenclator_id);
        if(empty($nomenclator)) return ['status' => 'error', 'msg' => 'No se encontró el nomenclador de la prestación'];


        ////Get the benefit's special condition (it may not have one)
        $this->db->select('SC.*,');
        $this->db->from('special_conditions SC');
        $this->db->where('SC.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $this->db->where('SC.plan_id',$valueBenefit->plan_id);
        $this->db->where('SC.provision',$valueBenefit->nomenclator_id);
        $this->db->where('period_since <=', $valueBenefit->period);
        $this->db->group_start();
        $this->db->where('period_until >=', $valueBenefit->period);
        $this->db->or_where('period_until', null);
        $this->db->group_end();
        $query = $this->db->get();

        if (!$query) return ['status' => 'error', 'msg' => 'Error inesperado al tratar de valorizar la prestación'];

        if($query->num_rows() !== 0) {
            $specialConditionID = $query->row()->id_special_conditions;
            $specialCondition = $this->SpecialConditions->get_specialconditions_by_id($specialConditionID);
        } else {
            $specialCondition = 0;
        }

        ////CASE 1)
        //Check if the benefit's unit price is different from 0. If so, use it to calculate the honorary and expenses and apply coverage
        if ($valueBenefit->unit_price != 0.00) {

            if($valueBenefit->billing_code_value == 1 || $valueBenefit->billing_code_value == 3){
                $honorary_calculated_value = ($valueBenefit->unit_price)*$valueBenefit->quantity;
                $expenses_calculated_value = 0;
            } else {
                $honorary_calculated_value = 0;
                $expenses_calculated_value = ($valueBenefit->unit_price)*$valueBenefit->quantity;
            }

            $coveredArray = $this->applyCoverage($honorary_calculated_value,$expenses_calculated_value,$valueBenefit,$specialCondition);
            if ($coveredArray['status'] == 'error') return ['status' => 'error', 'msg' => $coveredArray['msg']];
            return $this->saveValorizedBenefit($valueBenefit,$coveredArray['msg']);

        ////CASE 2)
        //Benefit is a surgery and benefit's additional is the same type as special condition's type
        } elseif($nomenclator->surgery) {
            if (!empty($specialCondition)) {

                if (($valueBenefit->additional == $specialCondition['type'])) {

                    $valueArray = $this->valueBenefitBySpecialCondition($valueBenefit, $specialCondition);
                    if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
                    $coveredArray = $this->applyCoverage($valueArray['msg']['honoraryValue'], $valueArray['msg']['expensesValue'], $valueBenefit, $specialCondition);
                    if ($coveredArray['status'] == 'error') return ['status' => 'error', 'msg' => $coveredArray['msg']];
                    return $this->saveValorizedBenefit($valueBenefit, $coveredArray['msg']);

                } else {

                    $valueArray = $this->valueBenefitByDefault($valueBenefit);
                    if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
                    $coveredArray = $this->applyCoverage($valueArray['msg']['honoraryValue'], $valueArray['msg']['expensesValue'], $valueBenefit, 0);
                    if ($coveredArray['status'] == 'error') return ['status' => 'error', 'msg' => $coveredArray['msg']];
                    return $this->saveValorizedBenefit($valueBenefit, $coveredArray['msg']);

                }

            } else {

                $valueArray = $this->valueBenefitByDefault($valueBenefit);
                if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
                $coveredArray = $this->applyCoverage($valueArray['msg']['honoraryValue'], $valueArray['msg']['expensesValue'], $valueBenefit, 0);
                if ($coveredArray['status'] == 'error') return ['status' => 'error', 'msg' => $coveredArray['msg']];
                return $this->saveValorizedBenefit($valueBenefit, $coveredArray['msg']);

            }

        ////CASE 3)
        //The benefit isn't a surgery but it has a special condition
        }elseif(!empty($specialCondition)){

            $valueArray = $this->valueBenefitBySpecialCondition($valueBenefit, $specialCondition);
            if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
            $coveredArray = $this->applyCoverage($valueArray['msg']['honoraryValue'], $valueArray['msg']['expensesValue'], $valueBenefit, $specialCondition);
            if ($coveredArray['status'] == 'error') return ['status' => 'error', 'msg' => $coveredArray['msg']];
            return $this->saveValorizedBenefit($valueBenefit, $coveredArray['msg']);

        ////CASE 4)
        //Default case
        } else {

            $valueArray = $this->valueBenefitByDefault($valueBenefit);
            if ($valueArray['status'] == 'error') return ['status' => 'error', 'msg' => $valueArray['msg']];
            $coveredArray = $this->applyCoverage($valueArray['msg']['honoraryValue'], $valueArray['msg']['expensesValue'], $valueBenefit, 0);
            if ($coveredArray['status'] == 'error') return ['status' => 'error', 'msg' => $coveredArray['msg']];
            return $this->saveValorizedBenefit($valueBenefit,$coveredArray['msg']);

        }

    }

    function valueBenefitBySpecialCondition($valueBenefit,$specialCondition){

        //Obtain unit value. First, obtain the benefit nomenclator
        $nomenclator = $this->Nomenclator->getNomenclatorById($valueBenefit->nomenclator_id);
        if(empty($nomenclator)) return ['status' => 'error', 'msg' => 'No se encontró el nomenclador de la prestación'];

        //Choose appropriate nomenclator's unit based on the benefit billing code
        $UH = $this->getUnitHonorary($nomenclator,$valueBenefit);

        //Calculate honoraries and units depending on the special condition group of values

        if($specialCondition['group_of_values'] == 1){ //1 = Especial

            if ($valueBenefit->internment_ambulatory_value == 0){ //0 = Ambulatorio

                if($specialCondition['type_of_values'] == 1){ // type of value 1 = $

                    if($valueBenefit->billing_code_value == 1){
                        $honorary_calculated_value = ($specialCondition['especiales'][0]['honorary']) * $valueBenefit->quantity;
                        $expenses_calculated_value = 0;
                    }elseif($valueBenefit->billing_code_value == 2){
                        $honorary_calculated_value = 0;
                        $expenses_calculated_value = ($specialCondition['especiales'][0]['expenses']) * $valueBenefit->quantity;
                    }else{
                        $honorary_calculated_value = ($specialCondition['especiales'][0]['honorary']) * $valueBenefit->quantity;
                        $expenses_calculated_value = ($specialCondition['especiales'][0]['expenses']) * $valueBenefit->quantity;
                    }

                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                } else { //type of value 0 = %

                    if($valueBenefit->billing_code_value == 1){
                        $honorary_calculated_value = (($specialCondition['especiales'][0]['honorary']/100)*$UH) * $valueBenefit->quantity;
                        $expenses_calculated_value = 0;
                    }elseif($valueBenefit->billing_code_value == 2){
                        $honorary_calculated_value = 0;
                        $expenses_calculated_value = (($specialCondition['especiales'][0]['expenses']/100)*$nomenclator->spending_unity) * $valueBenefit->quantity;
                    }else{
                        $honorary_calculated_value = (($specialCondition['especiales'][0]['honorary']/100)*$UH) * $valueBenefit->quantity;
                        $expenses_calculated_value = (($specialCondition['especiales'][0]['expenses']/100)*$nomenclator->spending_unity) * $valueBenefit->quantity;
                    }

                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                }

            } else {

                if($specialCondition['type_of_values'] == 1){ // type of value 1 = $

                    if($valueBenefit->billing_code_value == 1){
                        $honorary_calculated_value = ($specialCondition['especiales'][1]['honorary']) * $valueBenefit->quantity;
                        $expenses_calculated_value = 0;
                    }elseif($valueBenefit->billing_code_value == 2){
                        $honorary_calculated_value = 0;
                        $expenses_calculated_value = ($specialCondition['especiales'][1]['expenses']) * $valueBenefit->quantity;
                    }else{
                        $honorary_calculated_value = ($specialCondition['especiales'][1]['honorary']) * $valueBenefit->quantity;
                        $expenses_calculated_value = ($specialCondition['especiales'][1]['expenses']) * $valueBenefit->quantity;
                    }

                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                } else { //type of value 0 = %

                    if($valueBenefit->billing_code_value == 1){
                        $honorary_calculated_value = (($specialCondition['especiales'][1]['honorary']/100)*$UH) * $valueBenefit->quantity;
                        $expenses_calculated_value = 0;
                    }elseif($valueBenefit->billing_code_value == 2){
                        $honorary_calculated_value = 0;
                        $expenses_calculated_value = (($specialCondition['especiales'][1]['expenses']/100)*$nomenclator->spending_unity) * $valueBenefit->quantity;
                    }else{
                        $honorary_calculated_value = (($specialCondition['especiales'][1]['honorary']/100)*$UH) * $valueBenefit->quantity;
                        $expenses_calculated_value = (($specialCondition['especiales'][1]['expenses']/100)*$nomenclator->spending_unity) * $valueBenefit->quantity;
                    }

                    return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

                }

            }

        } else { //0 = unidad

            //Obtain the fee using benefit's unit
            $data = $this->getBenefitFee($valueBenefit);
            if($data['status'] == 'error') return ['status'=>'error','msg' => $data['msg']];

            $fee    = $data['msg']['fee'];
            $unit   = reset($data['msg']['unit']);  //Sanitize unit array

            $UG = $nomenclator->spending_unity;
            $AG = $unit['expenses'];

            //Obtain the fee using special condition's unit. Create a new benefit modifying unit and nomenclator
            $newValueBenefit = new stdClass();
            $newValueBenefit->medical_insurance_id = $valueBenefit->medical_insurance_id;
            $newValueBenefit->plan_id              = $valueBenefit->plan_id;
            $newValueBenefit->period               = $valueBenefit->period;
            $newValueBenefit->nomenclator_id       = $specialCondition['provision'];
            $newValueBenefit->unit                 = $specialCondition['unit'];

            $data = $this->getBenefitFee($valueBenefit);
            if($data['status'] == 'error') return ['status'=>'error','msg' => $data['msg']];

            $fee    = $data['msg']['fee'];
            $unit   = reset($data['msg']['unit']);  //Sanitize unit array

            //Obtain the professional
            $professional = $this->Professionals->getProfessionalsById($valueBenefit->id_professional_data)[0];  //There is only one professional, but the method returns array of arrays so I need the first one

            if ($fee['fee_type_id'] == 1) { //Arancel CMZ

                //Obtain the wanted honorary (the one which medical_career_id is the same as the professional medical_career_id)
                $wantedHonorary = array_filter($unit['honoraries'], array(new FilterHonoraryArray($professional['id_medical_career'], $professional['id_category_femeba']), 'equalsMedicalCareer'));
                $honorary = reset($wantedHonorary); //Sanitize

                if ($honorary['movement'] == 'F') { //$$
                    $honorary_calculated_value = $honorary['value'] * $valueBenefit->quantity;
                } else { // %%
                    $honorary_calculated_value = (($honorary['value'] / 100) * $specialCondition['quantity_units']) * $valueBenefit->quantity;
                }

            } else { //Arancel FEMEBA

                //Obtain the wanted honorary (the one which category_femeba_id is the same as the professional category_femeba_id
                $wantedHonorary = array_filter($unit['honoraries'], array(new FilterHonoraryArray($professional['id_medical_career'], $professional['id_category_femeba']), 'equalsCategoryFemeba'));
                $honorary = reset($wantedHonorary); //Sanitize

                if ($honorary['movement'] == 'F') { // $$
                    $honorary_calculated_value = $honorary['value'] * $valueBenefit->quantity;
                } else {
                    $honorary_calculated_value = ($honorary['value'] * $specialCondition['quantity_units']) * $valueBenefit->quantity;
                }

            }

            //Adjust values depending in billing code
            if($valueBenefit->billing_code_value == 1){
                $expenses_calculated_value = 0;
            }elseif($valueBenefit->billing_code_value == 2){
                $honorary_calculated_value = 0;
                $expenses_calculated_value = ($UG * $AG) * $valueBenefit->quantity;
            }else{
                $expenses_calculated_value = ($UG * $AG) * $valueBenefit->quantity;
            }

            return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

        }


    }

    function valueBenefitByDefault($valueBenefit){

        //Obtain the benefit's nomenclator
        $nomenclator = $this->Nomenclator->getNomenclatorById($valueBenefit->nomenclator_id);
        if(empty($nomenclator)) return ['status' => 'error', 'msg' => 'No se encontró el nomenclador de la prestación'];

        //Obtain the honorary's units
        $UH = $this->getUnitHonorary($nomenclator, $valueBenefit);

        //Obtain the fee using benefit's unit
        $data = $this->getBenefitFee($valueBenefit);
        if($data['status'] == 'error') return ['status'=>'error','msg' => $data['msg']];

        $fee    = $data['msg']['fee'];
        $unit   = reset($data['msg']['unit']);  //Sanitize unit array


        //Calculate expenses depending on the unit's movement
        if($unit['movement'] == 'F'){ // $$
            $expenses_calculated_value = $unit['expenses'] * $valueBenefit->quantity;
        }else{ // %%
            $expenses_calculated_value = ($nomenclator->spending_unity * ($unit['expenses'] / 100)) * $valueBenefit->quantity;
        }

        //Obtain the professional
        $professional = $this->Professionals->getProfessionalsById($valueBenefit->id_professional_data)[0];  //There is only one professional, but the method returns array of arrays so I need the first one

        if ($fee['fee_type_id'] == 1) { //Arancel CMZ

            //Obtain the wanted honorary (the one which medical_career_id is the same as the professional medical_career_id)
            $wantedHonorary = array_filter($unit['honoraries'], array(new FilterHonoraryArray($professional['id_medical_career'], $professional['id_category_femeba']), 'equalsMedicalCareer'));
            $honorary = reset($wantedHonorary); //Sanitize

            if ($honorary['movement'] == 'F') { //$$
                $honorary_calculated_value = $honorary['value'] * $valueBenefit->quantity;
            } else { // %%
                $honorary_calculated_value = (($honorary['value'] / 100) * $UH) * $valueBenefit->quantity;
            }

        } else { //Arancel FEMEBA

            //Obtain the wanted honorary (the one which category_femeba_id is the same as the professional category_femeba_id
            $wantedHonorary = array_filter($unit['honoraries'], array(new FilterHonoraryArray($professional['id_medical_career'], $professional['id_category_femeba']), 'equalsCategoryFemeba'));
            $honorary = reset($wantedHonorary); //Sanitize

            if ($honorary['movement'] == 'F') { // $$
                $honorary_calculated_value = $honorary['value'] * $valueBenefit->quantity;
            } else {
                $honorary_calculated_value = (($honorary['value'] / 100) * $UH) * $valueBenefit->quantity;
            }

        }

        //Adjust values depending in billing code
        if($valueBenefit->billing_code_value == 1){
            $expenses_calculated_value = 0;
        }elseif($valueBenefit->billing_code_value == 2){
            $honorary_calculated_value = 0;
        }else{
            //Do nothing, values already calculated
        }

        return ['status'=>'ok','msg' =>['honoraryValue' => $honorary_calculated_value, 'expensesValue' => $expenses_calculated_value]];

    }

    function applyCoverage($honorary_value,$expenses_value,$valueBenefit, $specialCondition){

        //Obtain the coverage of the benefit
        $this->db->select('C.*');
        $this->db->from('coverages C');
        $this->db->where('C.medical_insurance_id',$valueBenefit->medical_insurance_id);
        $this->db->where('C.plan_id',$valueBenefit->plan_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado al tratar de valorizar la prestación'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró una cobertura para la prestación a valorizar'];

        $coverage = $this->Coverages->getCoveragesById($query->row()->id_coverage);


        //Obtain the wanted unit coverage
        if(!empty($specialCondition)){

            if ((!empty($specialCondition['unit'])) && ($specialCondition['unit'] != $valueBenefit->unit)){

                $wantedUnitCoverageExpenses = array_filter($coverage['data'], array(new FilterUnitCoverageArray($valueBenefit->unit,$valueBenefit->internment_ambulatory_value), 'containsUnitAndType'));
                $unitCoverageExpenses = reset($wantedUnitCoverageExpenses);
                $wantedUnitCoverageHonoraries = array_filter($coverage['data'], array(new FilterUnitCoverageArray($specialCondition['unit'],$valueBenefit->internment_ambulatory_value), 'containsUnitAndType'));
                $unitCoverageHonorary = reset($wantedUnitCoverageHonoraries);

            } else {

                $wantedUnitCoverageExpenses = array_filter($coverage['data'], array(new FilterUnitCoverageArray($valueBenefit->unit,$valueBenefit->internment_ambulatory_value), 'containsUnitAndType'));
                $unitCoverageExpenses = reset($wantedUnitCoverageExpenses);
                $unitCoverageHonorary = $unitCoverageExpenses;

            }
        } else {

            $wantedUnitCoverageExpenses = array_filter($coverage['data'], array(new FilterUnitCoverageArray($valueBenefit->unit,$valueBenefit->internment_ambulatory_value), 'containsUnitAndType'));
            $unitCoverageExpenses = reset($wantedUnitCoverageExpenses);
            $unitCoverageHonorary = $unitCoverageExpenses;

        }

        if(empty($unitCoverageHonorary) || empty($unitCoverageExpenses)) return ['status' => 'error', 'msg' => 'No existe unidad de cobertura que coincida con la unidad de la prestación o de su condición especial (si tuviese)'];

        //Obtain the benefit's medical insurance
        $medicalInsurance = $this->MedicalInsurance->getInsuranceById($valueBenefit->medical_insurance_id);
        if (empty($medicalInsurance)) return ['status' => 'error', 'msg' => 'No se encontró la obra social que tiene asignada la prestación'];


        //1)Check for nocturne/holiday value to add additional value
        if($valueBenefit->holiday_value == 1){

            $additional = ($medicalInsurance->cobertura_fer_noct - 100)/100;
            $honorary_value = $honorary_value + ($honorary_value*$additional);
            $expenses_value = $expenses_value + ($expenses_value*$additional);

        }


        //2)Check if the benefit's medical insurance has maternal plan
        if($medicalInsurance->maternal_plan && $valueBenefit->maternal_plan_value) {

            $coveredHonorary = $honorary_value;
            $coveredExpenses = $expenses_value;

        //3)Check if the benefit modifies coverage
        }elseif ($valueBenefit->modify_coverage){

            $coveredHonorary = $honorary_value * ($valueBenefit->new_honorary / 100);
            $coveredExpenses = $expenses_value * ($valueBenefit->new_expenses / 100);

        //4)Default case: apply benefit's coverage values
        }else{

            $coveredHonorary = $honorary_value * ($unitCoverageHonorary['honorary'] / 100);
            $coveredExpenses = $expenses_value * ($unitCoverageExpenses['expense'] / 100);

        }


        //5) Apply multiple operation value
        if($valueBenefit->multiple_operation_value < 100){

            $coveredHonorary = $coveredHonorary * ($valueBenefit->multiple_operation_value / 100);
            $coveredExpenses = $coveredExpenses * ($valueBenefit->multiple_operation_value / 100);

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

        return ['status'=>'ok','msg' => $data ];

    }

    function getUnitHonorary($nomenclator,$valueBenefit){

        if($valueBenefit->additional == 1){
            return $nomenclator->speciality_unity;
        }elseif ($valueBenefit->additional == 2){
            return $nomenclator->help_unity;
        }else{
            return $nomenclator->anesthetist_unity;
        }

    }

    function getBenefitFee($valueBenefit){

        //Obtain the fee_type of the benefit fee using the medical_insurance_id
        $medicalInsurance = $this->MedicalInsurance->getInsuranceById($valueBenefit->medical_insurance_id);
        if (empty($medicalInsurance)) return ['status' => 'error', 'msg' => 'No se encontró la obra social que tiene asignada la prestación'];

        $benefit_fee_type = ($medicalInsurance->femeba == 1)? 2 : 1;  //If femeba = 1 (true), fee type is 2 because Arancel-Femeba has ID 2 in fee_types table

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

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado al tratar de valorizar la prestación'];
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
