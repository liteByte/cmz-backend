<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');


class Bill extends CI_Model{

    private $table      = "bill";
    private $table_d    = "bill_details_grouped";
    private $header;

    public function __construct(){
        parent::__construct();
    }

    /**
     * @param $data
     */

    public function bill_init($data){
        $date_billing  = $data['date_billing'];
        $id_medical_insurance = $data['id_medical_insurance'];

        $this->header = $this->getHeader($id_medical_insurance);
        $benefits = $this->getBenefits($id_medical_insurance);
        $total_result = $this->getTotal($id_medical_insurance);

    }


    /**
     * Search data for bill header
     * @param $id_medical
     * @return array|bool
     */
    private function getHeader($id_medical){
        $now =  $now = date('Y-m-d H:i:s');
        $this->db->select('settlement_name, address, location, postal_code, iva_id , cuit, payment_deadline, print ');
        $this->db->from('medical_insurance as mi');
        $this->db->where('mi.medical_insurance_id', $id_medical);
        $query =  $this->db->get();

        if(!$this->db->affected_rows()) return false;
        $header = (array)$query->row();
        $days = $header['payment_deadline'];
        $due_date = date('Y-m-d', strtotime($now. ' +  '. $days .' days'));
        $header += array('date_billing ' => $now);
        $header += array('due_date ' => $due_date);

        return $header;
    }

    /**
     * @param $id_medical
     * @return mixed
     */
    private function getBenefits($id_medical)     {

        $this->db->select('	');
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', 2);
        $this->db->order_by("period", "asc");
        $this->db->order_by("plan_id", "asc");
        $query = $this->db->get();


        /**
         * Group by Period
         */
        $lastPeriod = null;

        foreach ($query->result_array() as $row) {
            if ($row['period'] != $lastPeriod) {
                $lastPeriod = $row['period'];
                $orderPeriod[$lastPeriod][] = $row;
            } else {
                $orderPeriod[$lastPeriod][] = $row;
            }
        }

        /**
         * Group by Plans for each Period
         */

        $final_result = $orderPlan = [];
        $lastPlan = null;
        $parent_index = "plan_id";

        foreach ($orderPeriod as $plan => $p) {
            for($i= 0; $i<count($p); $i++){
                if($p[$i][$parent_index] != $lastPlan){
                    $lastPlan = $p[$i][$parent_index];
                    $orderPlan[$lastPlan][] = $p[$i];
                }else{
                    $orderPlan[$lastPlan][] = $p[$i];
                }
            }
            $final_result[$plan] = $orderPlan;
            $orderPlan = [];
        }

        return $final_result;
    }


    private function getTotal($id_medical){

        $this->db->select('medical_insurance_id, plan_id, period, 
                           SUM(value_honorary) AS Total_honorary, SUM(value_expenses) AS Total_expenses, count(benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', 2);
        $this->db->order_by("period", "asc");
        $this->db->order_by("plan_id", "asc");
        $this->db->group_by(array("period", "plan_id"));
        $query = $this->db->get();

        print_r($query->result_array());
        die();
    }



    /**
     * Helper to group array for any key
     * @param $arr
     * @param $key
     * @return array
     */
    public function array_group_by($arr, $key)    {
        if (!is_array($arr)) {
            trigger_error('array_group_by(): The first argument should be an array', E_USER_ERROR);
        }
        if (!is_string($key) && !is_int($key) && !is_float($key)) {
            trigger_error('array_group_by(): The key should be a string or an integer', E_USER_ERROR);
        }

        // Load the new array, splitting by the target key
        $grouped = [];
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }

        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array([$this,'array_group_by'] , $parms);
            }
        }
        return $grouped;
    }
}















//SELECT benefit_id, medical_insurance_id, plan_id, period, value_honorary, value_expenses , SUM(value_honorary) as H, state
//FROM `benefits`
//where state = 2
//GROUP BY period, plan_id
//ORDER by period ASC, plan_id ASC

























