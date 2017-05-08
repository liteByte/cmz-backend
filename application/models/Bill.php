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
        $branch_office        = $data['branch_office'];
        $date_billing         = $data['date_billing'];
        $form_type            = $data['form_type'];

        /**
         * Generate Number of Bill.
         */
        $this->number_bill = $this->generate_number_bill($branch_office);

        /**
         *  Get data for the header of bill and  Set type of PRINT..
         */
        $this->header = $this->getHeader($id_medical_insurance, $date_billing);
        if(empty($this->header)) return ['status' => 'error', 'msg' => 'No se pudo generar la cabecera de la factura'];


        //$this->type_of_print = 0;
        if($this->type_of_print == 1 ){  //1- Un numero de factura por obra social
            
            $totalOfPlansByPeriod = $this->getTotalForMedical($id_medical_insurance);
            $totalGeneral         = $this->getTotalGeneral($id_medical_insurance);
            $id_Bill              = $this->saveDataForBill($id_medical_insurance, $branch_office, $totalGeneral);

            if($id_Bill){
                
                $saveDetails = $this->saveDetails($id_Bill, $totalOfPlansByPeriod);
                // TODO send response to the front
                
            }

        }else{ //0- Un numero de factura por plan
            
            $totalOfPlansByPeriod = $this->getTotalForPlans($id_medical_insurance);
            $data                 = $this->saveDataForPlans($id_medical_insurance, $branch_office, $totalOfPlansByPeriod);

        }

    }

    /**
     * @param $id_medical_insurance
     * @param $branch_office
     * @param $totalGeneral
     */
    private function saveDataForBill($id_medical_insurance, $branch_office, $totalGeneral){
        $now = date('Y-m-d H:i:s');

        $dataOfBill = [
            'number_bill'           => $this->number_bill,
            'type_bill'             => $this->type_of_print,
            'branch_office'         => $branch_office,
            'type_document'         => 'abc',
            'type_form'             => 'c',
            'date_billing'          => $this->header['billing_date'],
            'date_created'          => $now,
            'date_due'              => $this->header['due_date'],
            'id_medical_insurance'  => $id_medical_insurance,
            'total'                 => $totalGeneral,
            'state_billing'         => 'c',
            'percentage_paid'       => 0,
            'annulled'              => 0
        ];

        $result = $this->db->insert($this->table, $dataOfBill);
        $errors = $this->db->error();


        if($result || $errors['code'] == 0){
            return $id_bill =  $this->db->insert_id();
        }else{
            return 0;
        }

    }

    private function saveDetails($id_Bill, $total_result){

        foreach ($total_result as $t){
            $data = [
                'id_bill'               => $id_Bill,
                'plan_id'               => $t['plan_id'],
                'billing_period'        => $t['period'],
                'total_honorary_period' => $t['total_honorary'],
                'total_expenses_period' => $t['total_expenses'],
                'total_benefit	'       => $t['total_benefit'],
            ];
            $this->db->insert($this->table_d, $data);
        }

        //Save the bill header
        $this->header['id_bill'] = $id_Bill;

        $this->db->insert('bill_header', $this->header);
        $errors = $this->db->error();

        if($errors['code'] != 0){
            return false;
        }

        return true;

    }

    private function saveDataForPlans($id_medical_insurance, $branch_office, $totalOfPlansByPeriod ){

        $now = date('Y-m-d H:i:s');
        $numberOfBill = $this->number_bill;
        foreach ($totalOfPlansByPeriod as $plans => $p){
            $dataOfBill = [
                'number_bill'           => $numberOfBill,
                'type_bill'             => $this->type_of_print,
                'branch_office'         => $branch_office,
                'type_document'         => 'abc',
                'type_form'             => 'c',
                'date_billing'          => $this->header['billing_date'],
                'date_created'          => $now,
                'date_due'              => $this->header['due_date'],
                'id_medical_insurance'  => $id_medical_insurance,
                'total'                 => '222222', // TODO get totol for any  plans
                'state_billing'         => 'c',
                'percentage_paid'       => 0,
                'annulled'              => 0
            ];

            $result = $this->db->insert($this->table, $dataOfBill);
            $errors = $this->db->error();

            if($result || $errors['code'] == 0){
                $id_bill =  $this->db->insert_id();
            }

            //Save the bill header
            $this->header['id_bill'] = $id_bill;

            $this->db->insert('bill_header', $this->header);
            $errors = $this->db->error();

            if($errors['code'] != 0){
                return false;
            }

            //For each benefit of the plan, save a detail
            foreach ($p as $per){

                //Save details
                $data = [
                    'id_bill'               => $id_bill,
                    'plan_id'               => $per['plan_id'],
                    'billing_period'        => $per['period'],
                    'total_honorary_period' => $per['total_honorary'],
                    'total_expenses_period' => $per['total_expenses'],
                    'total_benefit	'       => $per['total_benefit'],
                ];
                $result = $this->db->insert($this->table_d, $data);

            }
            $numberOfBill++;
        }

        return true;
    }


    /**
     * Search data for bill header
     * @param $id_medical
     * @return array|bool
     */
    private function getHeader($id_medical, $date_billing){
        
        $now = $date_billing;
        $this->db->select('mi.settlement_name, mi.address, mi.location, mi.postal_code, mi.iva_id , mi.cuit, mi.payment_deadline, mi.print, ides.description as iva_description');
        $this->db->from('medical_insurance as mi');
        $this->db->join('iva ides','mi.iva_id = ides.iva_id');
        $this->db->where('mi.medical_insurance_id', $id_medical);
        $query =  $this->db->get();

        if(!$this->db->affected_rows()) return [];
        
        $header = (array)$query->row();
        $days = $header['payment_deadline'];
        $due_date = date('Y-m-d', strtotime($now. ' +  '. $days .' days'));
        $header += array('date_billing' => $date_billing);
        $header += array('due_date' => $due_date);

        // Set type of print for Bill (for Medical I or Plans)
        $this->type_of_print = $header['print'];

        $headerData = [
            'id_bill'           => 0, //will be filled after bill is inserted
            'settlement_name'   => $header['settlement_name'],
            'address'           => $header['address'],
            'location'          => $header['location'],
            'postal_code'       => $header['postal_code'],
            'iva_description'   => $header['iva_description'],
            'cuit'              => $header['cuit'],
            'due_date'          => $header['due_date'],
            'billing_date'      => $header['date_billing'],
            'payment_deadline'  => $header['payment_deadline'],
            'print_type'        => $header['print'],
        ];

        return $headerData;
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
     *  Dependiendo lo que diga la obra social, voy a mostrar la factura solo por periodos o por Plan-Periodo
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

        //TODO Obtener el total por plan cuando se hace una factura por plan

        return $result;
    }

    /**
     * get total for plans when type of print eguals to 1 "medical Insu...."
     * @param $id_medical
     * @return mixed
     */
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
     * @param $branch_office
     * @return number of bill
     */
    private  function generate_number_bill ($branch_office ){

        $this->db->select_max('number_bill');
        $this->db->where('branch_office', $branch_office);
        $this->db->from('bill');
        $query = $this->db->get();

        foreach ($query->row() as $r){
            $result = $r;
        }

        // Check if it's the first bill
        if($result == 0){
            $result++;
        }else{
            $result++;
        }
        return $result;
        // TODO: Invoice length must be equals 8
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


















