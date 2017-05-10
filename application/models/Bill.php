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
    private $document_type;

    public function __construct(){
        parent::__construct();
    }

    public function bill_init($data){

        $id_medical_insurance = $data['id_medical_insurance'];
        $branch_office        = $data['branch_office'];
        $date_billing         = $data['date_billing'];
        $form_type            = $data['form_type'];
        $document_type        = $data['document_type'];

        //Load the document type
        $this->document_type = $document_type;

        /**
         * Generate bill number based on branch office, document form (A,B,C) and document type (generally F -> factura)
         */
        $this->number_bill = $this->generate_number_bill($branch_office,$form_type,$this->document_type);
        if(empty($this->number_bill)) return ['status' => 'error', 'msg' => 'No se pudo generar el número de factura'];


        /**
         *  Obtain the bill's header data
         */
        $this->header = $this->getHeader($id_medical_insurance, $date_billing);
        if(empty($this->header)) return ['status' => 'error', 'msg' => 'No se pudo generar la cabecera de la factura'];


        /**
         *  Obtain the medical insurance's print type and validate judicial
         */
        $this->db->select('mi.print, mi.judicial');
        $this->db->from('medical_insurance as mi');
        $this->db->where('mi.medical_insurance_id', $id_medical_insurance);
        $query =  $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudo obtener el tipo de impresión de la factura'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se pudo obtener el tipo de impresión de la factura'];

        if($query->row()->judicial == 1) return ['status' => 'error', 'msg' => 'No se pudo facturar: la obra social está en Proceso Judicial'];

        //If everything is OK, get the print type
        $this->type_of_print = $query->row()->print;


        /**
         *  Generate the bill in base of the print type (1->una factura por obra social completa y 2->una factura por plan)
         */
        if($this->type_of_print == 1 ){  //1- Una factura por obra social

            //Start transaction
            $this->db->trans_start();

                //Generate an array of every valued benefit that is going to be billed
                $totalOfPlansByPeriod = $this->getTotalForMedical($id_medical_insurance);
                if(empty($totalOfPlansByPeriod)) return ['status' => 'error', 'msg' => 'No se encontraron prestaciones valorizadas para la obra social ingresada'];

                //Generate the bill's total number
                $totalGeneral = $this->getTotalGeneral($id_medical_insurance);

                //Save the bill
                $id_Bill = $this->saveDataForBill($id_medical_insurance, $branch_office, $totalGeneral,$form_type);
                if(empty($id_Bill)) return ['status' => 'error', 'msg' => 'No se pudo generar la factura'];

                //Save the bill's detail and it's header
                if(!$this->saveDetails($id_Bill, $totalOfPlansByPeriod)) return ['status' => 'error', 'msg' => 'No se pudo generar el detalle de la factura'];

                //Update benefits of the medical insurance only if document type is not L
                if($this->document_type != 'L') {
                    if (!$this->updateBenefitsWithOneBillNumber($this->number_bill, $id_medical_insurance)) return ['status' => 'error', 'msg' => 'No se pudo actualizar el estado de las prestaciones de la obra social que se trato de facturar'];
                }

            //Close transaction
            $this->db->trans_complete();

            return ['status' => 'ok', 'msg' => 'Facturación de la obra social completada satisfactoriamente'];


        }else{ //0- Un numero de factura por plan de la obra social

            //Start transaction
            $this->db->trans_start();

                //Generate an array of every valued benefit that is going to be billed, group by plan
                $totalOfPlansByPeriod = $this->getTotalForPlans($id_medical_insurance);
                if(empty($totalOfPlansByPeriod)) return ['status' => 'error', 'msg' => 'No se encontraron prestaciones valorizadas para la obra social ingresada'];

                //Save the bill, bill's detail and bill's header for each plan. Update the benefits
                $result = $this->saveDataForPlans($id_medical_insurance, $branch_office, $totalOfPlansByPeriod,$form_type);
                if($result != "ok") return ['status' => 'error', 'msg' => $result];

            //Close transaction
            $this->db->trans_complete();

            return ['status' => 'ok', 'msg' => 'Facturación de la obra social completada satisfactoriamente'];

        }

    }

    //Save only one bill for a medical insurance
    private function saveDataForBill($id_medical_insurance, $branch_office, $totalGeneral,$form_type){
        $now = date('Y-m-d H:i:s');

        $dataOfBill = [
            'number_bill'           => $this->number_bill,
            'type_bill'             => $this->type_of_print,
            'branch_office'         => $branch_office,
            'type_document'         => $this->document_type,
            'type_form'             => $form_type,
            'date_billing'          => $this->header['billing_date'],
            'date_created'          => $now,
            'date_due'              => $this->header['due_date'],
            'id_medical_insurance'  => $id_medical_insurance,
            'total'                 => $totalGeneral,
            'state_billing'         => 1,
            'percentage_paid'       => 0,
            'annulled'              => 0
        ];

        $this->db->insert($this->table, $dataOfBill);
        $errors = $this->db->error();

        if($errors['code'] == 0){
            return $id_bill =  $this->db->insert_id();
        }else{
            return 0;
        }

    }

    //Save the detail of a bill made by medical insurance and its header
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
            if ($this->db->affected_rows() == 0) return false;

        }

        //Save the bill header
        if(!$this->saveBillHeader($id_Bill)) return false;

        return true;

    }

    //Save a bill's header
    public function saveBillHeader($bill_id){

        $this->header['id_bill'] = $bill_id;

        $this->db->insert('bill_header', $this->header);
        $errors = $this->db->error();

        if($errors['code'] != 0){
            return false;
        }else{
            return true;
        }

    }

    //Save a bill for every plan of a medical insurance. Save the detail of every bill and its header.
    private function saveDataForPlans($id_medical_insurance, $branch_office, $totalOfPlansByPeriod,$form_type ){

        $now = date('Y-m-d H:i:s');
        $numberOfBill = $this->number_bill;

        foreach ($totalOfPlansByPeriod as $plans => $p){

            //For each plan, obtain the sum of every honorary + expense for all periods
            $planTotal = 0;
            foreach ($p as $specificPlan){
                $planTotal += $specificPlan['total_honorary'] + $specificPlan['total_expenses'];
            }

            $dataOfBill = [
                'number_bill'           => $numberOfBill,
                'type_bill'             => $this->type_of_print,
                'branch_office'         => $branch_office,
                'type_document'         => $this->document_type,
                'type_form'             => $form_type,
                'date_billing'          => $this->header['billing_date'],
                'date_created'          => $now,
                'date_due'              => $this->header['due_date'],
                'id_medical_insurance'  => $id_medical_insurance,
                'total'                 => $planTotal,
                'state_billing'         => 1,
                'percentage_paid'       => 0,
                'annulled'              => 0
            ];

            $result = $this->db->insert($this->table, $dataOfBill);
            $errors = $this->db->error();

            if($errors['code'] != 0) return "No se pudo generar la factura";

            //Obtengo el ID de la factura
            $id_bill =  $this->db->insert_id();

            //Save the bill header
            if(!$this->saveBillHeader($id_bill)) return "No se pudo generar el encabezado de la factura";

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

                $this->db->insert($this->table_d, $data);
                if ($this->db->affected_rows() == 0) return "No se pudo generar el detalle de la factura";

            }

            //Update benefits of the medical insurance only if document type is not L
            if($this->document_type != 'L') {
                if (!$this->updateBenefitsWithManyBillNumber($numberOfBill, $id_medical_insurance, $p[0]['plan_id'])) return "No se pudo actualizar el estado de las prestaciones de la obra social que se trato de facturar";
            }

            $numberOfBill++;
        }

        return "ok";
    }

    //Get the bill header's data
    private function getHeader($id_medical, $date_billing){

        $now = $date_billing;
        $this->db->select('mi.settlement_name, mi.address, mi.location, mi.postal_code, mi.iva_id , mi.cuit, mi.payment_deadline, mi.print, ides.description as iva_description');
        $this->db->from('medical_insurance as mi');
        $this->db->join('iva ides','mi.iva_id = ides.iva_id');
        $this->db->where('mi.medical_insurance_id', $id_medical);
        $query =  $this->db->get();

        if($query->num_rows() <= 0) return [];

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
     * Get Total for "Plans" group by "ID_plan - Period"
     *  Dependiendo lo que diga la obra social, voy a mostrar la factura solo por periodos o por Plan-Periodo
     * @param $id_medical
     * @return mixed
     */
    private function getTotalForMedical($id_medical){

        //If document type is L, the medical insurance is OSDE and it was billed before, so we select the benefits
        //with state = 2 (billed state)
        $this->db->select('medical_insurance_id, plan_id, period, 
                           SUM(value_honorary) AS total_honorary, SUM(value_expenses) AS total_expenses, count(benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', ($this->document_type == 'L')? 2:1);
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

        //If document type is L, the medical insurance is OSDE and it was billed before, so we select the benefits
        //with state = 2 (billed state)
        $this->db->select('benefit_id, plan_id, period, value_honorary, value_expenses, value_unit,
           SUM(value_honorary) AS total_honorary, SUM(value_expenses) AS total_expenses, count(benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', ($this->document_type == 'L')? 2:1);
        $this->db->order_by("plan_id", "asc");
        $this->db->order_by("period", "asc");
        $this->db->group_by(array("plan_id", "period"));
        $query = $this->db->get();
        $result = $query->result_array();
        $result = $this->array_group_by($result, 'plan_id');

        return $result;
    }

    /**
     * get total for plans when type of print eguals to 1 "medical Insu...."
     * @param $id_medical
     * @return mixed
     */
    private function getTotalGeneral($id_medical){

        //If document type is L, the medical insurance is OSDE and it was billed before, so we select the benefits
        //with state = 2 (billed state)
        $this->db->select('SUM(value_honorary) +  SUM(value_expenses) as total_benefit', FALSE);
        $this->db->from('benefits');
        $this->db->where('medical_insurance_id', $id_medical);
        $this->db->where('state', ($this->document_type == 'L')? 2:1);
        $query = $this->db->get();
        foreach ($query->row() as $total){
            $result = $total;
        }
        return $result;
    }

    //Generate the correct bill number based on Branch Office + Document Type + Document Form
    private  function generate_number_bill ($branch_office,$form_type,$document_type ){

        $this->db->select_max('number_bill');
        $this->db->where('branch_office', $branch_office);
        $this->db->where('type_document', $document_type);
        $this->db->where('type_form', $form_type);
        $this->db->from('bill');
        $query = $this->db->get();

        foreach ($query->row() as $r){
            $result = $r;
        }

        if (empty($result)) $result = 0;

        // Add 1 to the number obtained so we get the next bill number
        $result ++;

        return $result;

    }

    //Update all benefits of a certain medical insurance with the same bill number
    private function updateBenefitsWithOneBillNumber($bill_number,$medical_insurance_id){

        $data = array(
            'state'       => 2,
            'bill_number' => $bill_number
        );

        $this->db->where('medical_insurance_id', $medical_insurance_id);
        $this->db->where('state', 1);
        $this->db->update('benefits', $data);

        if ($this->db->affected_rows() == 0) return false;

        return true;
    }

    //Update a benefit of a certain medical insurance and plan
    private function updateBenefitsWithManyBillNumber($bill_number,$medical_insurance_id,$plan_id){

        $data = array(
            'state'       => 2,
            'bill_number' => $bill_number
        );

        $this->db->where('medical_insurance_id', $medical_insurance_id);
        $this->db->where('plan_id', $plan_id);
        $this->db->where('state', 1);
        $query = $this->db->update('benefits', $data);

        if (!$query)                            return false;
        if ($this->db->affected_rows() == 0)    return false;

        return true;
    }

    //Get all information needed to print
    public function getPrintData($billID){

        $billData = [];

        //Get bill general information
        $this->db->select('B.*');
        $this->db->from('bill B');
        $this->db->where('B.id_bill', $billID);
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudieron obtener los datos de la factura'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron datos para la factura que desea imprimir'];

        $billData ['generalInformation'] = $query->result_array()[0];


        //Get bill header
        $this->db->select('BH.*');
        $this->db->from('bill_header BH');
        $this->db->where('BH.id_bill', $billID);
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudieron obtener los datos de la cabecera de la factura'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron datos para cabecera de la factura'];

        $billData ['header'] = $query->result_array()[0];


        //Get bill body information
        if($billData ['generalInformation']['type_bill'] == 0) {

            $this->db->select('BB.*');
            $this->db->from('bill_details_grouped BB');
            $this->db->where('BB.id_bill', $billID);
            $this->db->order_by("BB.billing_period", "asc");
            $this->db->order_by("BB.plan_id", "asc");
            $query = $this->db->get();

            if (!$query) return ['status' => 'error', 'msg' => 'No se pudieron obtener los datos del cuerpo de la factura'];
            if ($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron datos para el cuerpo de la factura'];

            $billData ['body'] = $query->result_array();

        }else{

            $this->db->select('sum(total_honorary_period) as total_honorary,
	                           sum(total_expenses_period) as total_expenses,
                               sum(total_benefit) as total_benefit,
                               billing_period');
            $this->db->from('bill_details_grouped BB');
            $this->db->where('BB.id_bill', $billID);
            $this->db->group_by('BB.billing_period');
            $this->db->order_by("BB.billing_period", "asc");
            $query = $this->db->get();

            if (!$query) return ['status' => 'error', 'msg' => 'No se pudieron obtener los datos del cuerpo de la factura'];
            if ($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron datos para el cuerpo de la factura'];

            $billData ['body'] = $query->result_array();

        }

        return ['status' => 'ok', 'msg' => $billData];

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


















