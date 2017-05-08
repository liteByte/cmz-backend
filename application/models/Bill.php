<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');

class Bill extends CI_Model{

    private $table      = "bill";
    private $table_d    = "bill_details_grouped";
    private $header;
    private $number_bill;
    private $type_of_print;
    private $benefits;

    public function __construct(){
        parent::__construct();
    }

    public function bill_init($data){
        $id_medical_insurance = $data['id_medical_insurance'];
        $branch_officce = $data['branch_officce'];
        $date_billing  = $data['date_billing'];

        /**
         * Generate Number of Bill.
         */
        $this->number_bill = $this->generate_number_bill($id_medical_insurance, $branch_officce);

        /**
         *  Get data for the header of bill and  Set type of PRINT..
         */
        $this->header = $this->getHeader($id_medical_insurance, $date_billing);

        /**
         * Get data group by period and plans  TODO: is necesary???
         */
        /*$this->benefits = $this->getBenefits($id_medical_insurance);*/

//        $this->type_of_print = 0;
        if($this->type_of_print == 1 ){
            $totalOfPlansbyPeriod = $this->getTotalForMedical($id_medical_insurance);
            $totalGeneral = $this->getTotalGeneral($id_medical_insurance);
            $id_Bill = $this->saveDataForBill($id_medical_insurance, $branch_officce, $totalGeneral);

            if($id_Bill){
                $saveDetails = $this->saveDetails($id_Bill,  $totalOfPlansbyPeriod );
            }

        }else{

            /// TODO Create Bill for Plans
            /// TODO Each plan has its own bill number
            /// TODO Calculate total for each plan
            $totalOfPlansbyPeriod = $this->getTotalForPlans($id_medical_insurance);
            $data = $this->saveDataForPlans($id_medical_insurance, $branch_officce, $totalOfPlansbyPeriod);


        }

    }




    /**
     * @param $id_medical_insurance
     * @param $branch_officce
     * @param $totalGeneral
     */
    private function saveDataForBill($id_medical_insurance, $branch_officce, $totalGeneral){
        $now = date('Y-m-d H:i:s');

        $dataOfBill = [
            'number_bill'       => $this->number_bill,
            'type_bill'         => $this->type_of_print,
            'branch_officce'    => $branch_officce,
            'type_document'     => 'abc',
            'type_form'         => 'c',
            'date_billing'      => $this->header['date_billing'],
            'date_created'      => $now,
            'date_due'          => $this->header['due_date'],
            'id_medical_insurance'  => $id_medical_insurance,
            'total'             => $totalGeneral,
            'state_billing'     => 'c',
            'percentage_paid'   => 0,
            'annulled'          => 0
        ];

        $result = $this->db->insert($this->table, $dataOfBill);
        $errors = $this->db->error();

        if($result || $errors['code'] == 0){
            return $id_bill =  $this->db->insert_id();
        }
    }

    private function saveDetails($id_Bill, $total_result){

        foreach ($total_result as $t){
            $data = [
                'id_bill' => $id_Bill,
                'plan_id' => $t['plan_id'],
                'billing_period' => $t['period'],
                'total_honorary_period' =>$t['total_honorary'],
                'total_expenses_period	' => $t['total_expenses'],
                'total_benefit	' => $t['total_benefit'],
            ];
            $result = $this->db->insert($this->table_d, $data);
        }
    }

    private function saveDataForPlans($id_medical_insurance, $branch_officce, $totalOfPlansbyPeriod ){

        $now = date('Y-m-d H:i:s');
        $numberOfBill = $this->number_bill;
        foreach ($totalOfPlansbyPeriod as $plans => $p){
            $dataOfBill = [
                'number_bill'       => $numberOfBill,
                'type_bill'         => $this->type_of_print,
                'branch_officce'    => $branch_officce,
                'type_document'     => 'abc',
                'type_form'         => 'c',
                'date_billing'      => $this->header['date_billing'],
                'date_created'      => $now,
                'date_due'          => $this->header['due_date'],
                'id_medical_insurance'  => $id_medical_insurance,
                'total'             => '222222', // TODO get totol for any  plans
                'state_billing'     => 'c',
                'percentage_paid'   => 0,
                'annulled'          => 0
            ];

            $result = $this->db->insert($this->table, $dataOfBill);
            $errors = $this->db->error();

            if($result || $errors['code'] == 0){
              $id_bill =  $this->db->insert_id();
            }

            foreach ($p as $per){
                //TODO save Details
                $data = [
                    'id_bill' => $id_bill,
                    'plan_id' => $per['plan_id'],
                    'billing_period' => $per['period'],
                    'total_honorary_period' =>$per['total_honorary'],
                    'total_expenses_period	' => $per['total_expenses'],
                    'total_benefit	' => $per['total_benefit'],
                ];
                $result = $this->db->insert($this->table_d, $data);
            }
            $numberOfBill++;
        }

//        foreach ($totalOfPlansbyPeriod as $t){
//            print_r($t);
//            $total =  $t['total_honorary'] + $t['total_expenses'];
//            $number_bill =  $this->number_bill++;
//
//            $dataOfBill = [
//                'number_bill'       => $this->number_bill,
//                'type_bill'         => $this->type_of_print,
//                'branch_officce'    => $branch_officce,
//                'type_document'     => 'abc',
//                'type_form'         => 'c',
//                'date_billing'      => $this->header['date_billing'],
//                'date_created'      => $now,
//                'date_due'          => $this->header['due_date'],
//                'id_medical_insurance'  => $id_medical_insurance,
//                'total'             => $total,
//                'state_billing'     => 'c',
//                'percentage_paid'   => 0,
//                'annulled'          => 0
//            ];
//
//            $result = $this->db->insert($this->table, $dataOfBill);
//            $id_bill =  $this->db->insert_id();
//
//            $data = [
//                'id_bill' => $id_bill,
//                'plan_id' => $t['plan_id'],
//                'billing_period' => $t['period'],
//                'total_honorary_period' =>$t['total_honorary'],
//                'total_expenses_period	' => $t['total_expenses'],
//                'total_benefit	' => $t['total_benefit'],
//            ];
//            $result = $this->db->insert($this->table_d, $data);
        }


    /**
     * Search data for bill header
     * @param $id_medical
     * @return array|bool
     */
    private function getHeader($id_medical, $date_billing){
        $now =  $date_billing;
        $this->db->select('settlement_name, address, location, postal_code, iva_id , cuit, payment_deadline, print ');
        $this->db->from('medical_insurance as mi');
        $this->db->where('mi.medical_insurance_id', $id_medical);
        $query =  $this->db->get();

        if(!$this->db->affected_rows()) return false;
        $header = (array)$query->row();
        $days = $header['payment_deadline'];
        $due_date = date('Y-m-d', strtotime($now. ' +  '. $days .' days'));
        $header += array('date_billing' => $date_billing);
        $header += array('due_date' => $due_date);

        // Set type of print for Bill (for Medical I or Plans)
        $this->type_of_print = $header['print'];

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

    /**
     * Get Total for "Plans" group by "ID_plan - Period"
     * @param $id_medical
     * @return mixed
     */
    private function getTotalForMedical($id_medical){

        $this->db->select('medical_insurance_id, plan_id, period, 
                           SUM(value_honorary) AS total_honorary, SUM(value_expenses) AS total_expenses, count(benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', 2);
        $this->db->order_by("period", "asc");
        $this->db->order_by("plan_id", "asc");
        $this->db->group_by(array("period", "plan_id"));
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    /**
     * Get Total for "Plans" group by "Period - ID_plan "
     * @param $id_medical
     * @return mixed
     */
    private function getTotalForPlans($id_medical){

        $this->db->select('benefit_id, plan_id, period, value_honorary, value_expenses, value_unit,
           SUM(value_honorary) AS total_honorary, SUM(value_expenses) AS total_expenses, count(benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', 2);
        $this->db->order_by("plan_id", "asc");
        $this->db->order_by("period", "asc");
        $this->db->group_by(array("plan_id", "period"));
        $query = $this->db->get();
        $result = $query->result_array();
        $result = $this->array_group_by($result, 'plan_id');
        return $result;
    }


    private function getTotalGeneral($id_medical){

        $this->db->select('SUM(value_honorary) +  SUM(value_expenses) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', 2);
        $query = $this->db->get();
        foreach ($query->row() as $total){
            $result = $total;
        }
        return $result;
    }

    /**
     * @param $id_medical
     * @param $branch_officce
     * @return number of bill
     */
    private  function generate_number_bill ($id_medical, $branch_officce ){
        $this->db->select_max('number_bill');
        $this->db->where('branch_officce', $branch_officce);
        $this->db->from('bill');
        $query = $this->db->get();
        foreach ($query->row() as $r){
            $result = $r;
        }

        // If is the first bill
        if($result == 0){
            $result++;
        }else{
            $result++;
        }
        return $result;
        // TODO: Invoice length must be equals  8
    }


    /**
     * Help to group by array for any key
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


















