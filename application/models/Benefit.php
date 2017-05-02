<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Benefit extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->model('nomenclator');
    }

    //Creates the benefit in 'benefits'
    public function save($medical_insurance_id, $plan_id, $id_professional_data, $period, $remesa, $additional, $nomenclator_id, $quantity, $billing_code_id, $multiple_operation_value, $holiday_option_id, $maternal_plan_option_id, $internment_ambulatory_option_id, $unit_price, $benefit_date, $affiliate_id, $bill_number, $modify_coverage, $new_honorary, $new_expenses,$value_honorary, $value_expenses){

        //Get benefit unit from it's nomenclator
        $nomenclator = $this->nomenclator->getNomenclatorById($nomenclator_id);

        $data = array(
            'medical_insurance_id'             => $medical_insurance_id,
            'plan_id'                          => $plan_id,
            'id_professional_data'             => $id_professional_data,
            'period'                           => $period,
            'remesa'                           => (empty($remesa)                                       ? null : $remesa),
            'additional'                       => (empty($additional)                                   ? null : $additional),
            'nomenclator_id'                   => $nomenclator_id,
            'quantity'                         => $quantity,
            'billing_code_id'                  => $billing_code_id,
            'multiple_operation_value'         => $multiple_operation_value,
            'holiday_option_id'                => (empty($holiday_option_id)                            ? null : $holiday_option_id),
            'maternal_plan_option_id'          => (empty($maternal_plan_option_id)                      ? null : $maternal_plan_option_id),
            'internment_ambulatory_option_id'  => $internment_ambulatory_option_id,
            'unit_price'                       => ((empty($unit_price) && $unit_price !== '0')          ? null : $unit_price),
            'benefit_date'                     => (empty($benefit_date)                                 ? null : $benefit_date),
            'affiliate_id'                     => (empty($affiliate_id)                                 ? null : $affiliate_id),
            'bill_number'                      => (empty($bill_number)                                  ? null : $bill_number),
            'modify_coverage'                  => (empty($modify_coverage) && $modify_coverage !== '0'  ? null : $modify_coverage),
            'new_honorary'                     => (empty($new_honorary) && $new_honorary !== '0'        ? null : $new_honorary),
            'new_expenses'                     => (empty($new_expenses) && $new_expenses !== '0'        ? null : $new_expenses),
            'value_honorary'                   => ((empty($value_honorary) && $value_honorary !== 0)    ? null : $value_honorary),
            'value_expenses'                   => ((empty($value_expenses) && $value_expenses !== 0)    ? null : $value_expenses),
            'value_unit'                       => (empty($bill_number)                                  ? null : $nomenclator->unity),
            'state'                            => (empty($bill_number)                                  ? 1 : 2),
            'active'                           => 'active'
        );

        $this->db->insert('benefits', $data);
        if ($this->db->affected_rows() == 0) return false;

        return true;

    }

    //Updates the benefit in 'benefits'
    public function update($remesa, $additional, $quantity, $billing_code_id, $multiple_operation_value, $holiday_option_id, $maternal_plan_option_id, $internment_ambulatory_option_id, $unit_price, $benefit_date, $affiliate_id, $bill_number, $modify_coverage, $new_honorary, $new_expenses, $value_honorary, $value_expenses, $id, $userID){

        $query = $this->db->get_where('benefits', ["benefit_id" => $id, "state" => 1]);

        if($query->num_rows()){

            //Get benefit unit from it's nomenclator
            $nomenclator = $this->nomenclator->getNomenclatorById($query->row()->nomenclator_id);

            $now = date('Y-m-d H:i:s');
            $data = array(
                'remesa'                           => (empty($remesa)                                       ? null : $remesa),
                'additional'                       => (empty($additional)                                   ? null : $additional),
                'quantity'                         => $quantity,
                'billing_code_id'                  => $billing_code_id,
                'multiple_operation_value'         => $multiple_operation_value,
                'holiday_option_id'                => (empty($holiday_option_id)                            ? null : $holiday_option_id),
                'maternal_plan_option_id'          => (empty($maternal_plan_option_id)                      ? null : $maternal_plan_option_id),
                'internment_ambulatory_option_id'  => $internment_ambulatory_option_id,
                'unit_price'                       => ((empty($unit_price) && $unit_price !== '0')          ? null : $unit_price),
                'benefit_date'                     => (empty($benefit_date)                                 ? null : $benefit_date),
                'affiliate_id'                     => (empty($affiliate_id)                                 ? null : $affiliate_id),
                'bill_number'                      => (empty($bill_number)                                  ? null : $bill_number),
                'modify_coverage'                  => (empty($modify_coverage) && $modify_coverage !== '0'  ? null : $modify_coverage),
                'new_honorary'                     => (empty($new_honorary) && $new_honorary !== '0'        ? null : $new_honorary),
                'new_expenses'                     => (empty($new_expenses) && $new_expenses !== '0'        ? null : $new_expenses),
                'value_honorary'                   => ((empty($value_honorary) && $value_honorary !== 0)    ? null : $value_honorary),
                'value_expenses'                   => ((empty($value_expenses) && $value_expenses !== 0)    ? null : $value_expenses),
                'value_unit'                       => (empty($bill_number)                                  ? null : $nomenclator->unity),
                'state'                            => (empty($bill_number)                                  ? 1    : 2),
                'active'                           => 'active',
                'update_date'                      => $now,
                'modify_user_id'                   => $userID
            );

            $this->db->where('benefit_id', $id);
            $this->db->update('benefits', $data);

        }else{

            return ['status' => 'error', 'msg' => 'Esta prestación no puede ser modificada, porque la misma ya ha sido facturada'];

        }

        return ['status' => 'ok', 'msg' => 'Prestación actualizada satisfactoriamente'];
    }

    //Get all benefits
    public function getBenefits(){

        $result = array();

        $this->db->select('B.benefit_id, B.medical_insurance_id, MI.denomination as medical_insurance_denom, B.plan_id, PL.description as plan_description, B.period, B.id_professional_data, PF.registration_number, PF.name, CONCAT(N.code,"/",IFNULL(N.class,"-")) as benefit, B.nomenclator_id, N.description as nomenclator_description, B.quantity, B.unit_price, B.state');
        $this->db->from('benefits B');
        $this->db->join('medical_insurance MI',         'B.medical_insurance_id = MI.medical_insurance_id');
        $this->db->join('plans PL',                     'B.plan_id = PL.plan_id');
        $this->db->join('professionals PF',             'B.id_professional_data = PF.id_professional_data');
        $this->db->join('nomenclators N',               'B.nomenclator_id = N.nomenclator_id');
        $this->db->order_by("MI.denomination", "asc");
        $this->db->order_by("PL.description", "asc");
        $this->db->order_by("B.period", "desc");
        $this->db->order_by("PF.registration_number", "asc");
        $this->db->order_by("benefit", "asc");
        $this->db->where('B.active',"active");
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        foreach ($query->result_array('Benefit') as $row){
            $result[] = $row;
        }

        return $result;

    }

    //Get a specific benefit information
    public function getBenefitById($benefitID){

        $this->db->select('B.*,N.surgery');
        $this->db->from('benefits B');
        $this->db->join('nomenclators N','B.nomenclator_id = N.nomenclator_id');
        $this->db->where('B.benefit_id',$benefitID);
        $query = $this->db->get();

        return $query->row();

    }

    //Delete benefit information in 'benefits'
    public function delete($benefitID,$userID){

        $now = date('Y-m-d H:i:s');

        $query = $this->db->get_where('benefits', ["benefit_id" => $benefitID, "state" => 1]);

        if($query->num_rows()){

            //Delete benefit
            $this->db->where('benefit_id', $benefitID);
            $result = $this->db->delete('benefits');
            $errors = $this->db->error();
            if($errors['code'] == '1451')   return ['status' => 'error', 'msg' => 'No se puede eliminar la prestación ya que contiene información relacionada'];
            if(!$result)                    return ['status' => 'error', 'msg' => 'Error inesperado al intentar eliminar la prestación'];

        } else {

            return ['status' => 'error', 'msg' => 'Esta prestación no puede ser eliminada, porque la misma ya ha sido facturada'];

        }

        return ['status' => 'ok', 'msg' => 'Prestación eliminada satisfactoriamente'];
    }

    public function validateData($medical_insurance_id, $plan_id, $id_professional_data, $period, $nomenclator_id){

        //Repeated key validation
        $query = $this->db->get_where('benefits', ['medical_insurance_id' => $medical_insurance_id, 'plan_id' => $plan_id, 'id_professional_data' => $id_professional_data, 'period' => $period, 'nomenclator_id' => $nomenclator_id]);
        if ($query->num_rows() > 0) return "Ya existe una prestación con la misma combinación de OS + Plan + Matricula + Prestación + Período";

        return $this->validateIDs($medical_insurance_id, $plan_id, $id_professional_data, $nomenclator_id);

    }

    public function validateIDs($medical_insurance_id, $plan_id, $id_professional_data, $nomenclator_id){

        //Medical insurance existence validation
        $query = $this->db->get_where('medical_insurance', ["medical_insurance_id" => $medical_insurance_id]);
        if ($query->num_rows() <= 0) return "No existe la obra social especificada";

        //Plan existence validation
        $query = $this->db->get_where('plans', ["plan_id" => $plan_id]);
        if ($query->num_rows() <= 0) return "No existe el plan especificado";

        //Professional existence validation
        $query = $this->db->get_where('professionals', ["id_professional_data" => $id_professional_data]);
        if ($query->num_rows() <= 0) return "No existe la matrícula de profesional especificada";

        //Nomenclator existence validation
        $query = $this->db->get_where('nomenclators', ["nomenclator_id" => $nomenclator_id]);
        if ($query->num_rows() <= 0) return "No existe el nomenclador especificado";

        return "OK";

    }

}
