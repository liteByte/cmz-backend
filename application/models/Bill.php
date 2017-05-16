<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');

class Bill extends CI_Model{

    private $table      = "bill";
    private $table_d    = "bill_details_grouped";
    private $header;
    private $number_bill;
    private $type_of_print;
    private $date_billing;
    private $benefits;
    private $document_type;

    public function __construct(){
        parent::__construct();
        $this->load->library('numbertoletter');
    }

    public function bill_init($data){

        $id_medical_insurance = $data['id_medical_insurance'];
        $branch_office        = $data['branch_office'];
        $date_billing         = $data['date_billing'];
        $form_type            = $data['form_type'];
        $document_type        = $data['document_type'];

        //Load the document type
        $this->document_type = $document_type;
        $this->date_billing  = $date_billing;

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
                if(empty($totalOfPlansByPeriod)) return ['status' => 'error', 'msg' => 'No se encontraron prestaciones valorizadas para realizar la factura '.$this->document_type.' de la obra social ingresada'];

                //Generate the bill's total number
                $totalGeneral = $this->getTotalGeneral($id_medical_insurance);

                //Save the bill
                $id_Bill = $this->saveDataForBill($id_medical_insurance, $branch_office, $totalGeneral,$form_type);
                if(empty($id_Bill)) return ['status' => 'error', 'msg' => 'No se pudo generar la factura'];

                //Save the bill's detail and it's header
                if(!$this->saveDetails($id_Bill, $totalOfPlansByPeriod)) return ['status' => 'error', 'msg' => 'No se pudo generar el detalle de la factura'];

                //Update benefits of the medical insurance
                if (!$this->updateBenefitsWithOneBillNumber($this->number_bill, $id_medical_insurance, $id_Bill)) return ['status' => 'error', 'msg' => 'No se pudo actualizar el estado de las prestaciones de la obra social que se trato de facturar'];

            //Close transaction
            $this->db->trans_complete();

            return ['status' => 'ok', 'msg' => 'Facturación de la obra social completada satisfactoriamente'];


        }else{ //0- Un numero de factura por plan de la obra social

            //Start transaction
            $this->db->trans_start();

                //Generate an array of every valued benefit that is going to be billed, group by plan
                $totalOfPlansByPeriod = $this->getTotalForPlans($id_medical_insurance);
                if(empty($totalOfPlansByPeriod)) return ['status' => 'error', 'msg' => 'No se encontraron prestaciones valorizadas para realizar la factura '.$this->document_type.' de la obra social ingresada'];

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
            'amount_paid'           => 0,
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
                'amount_paid'           => 0,
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
            if (!$this->updateBenefitsWithManyBillNumber($numberOfBill, $id_medical_insurance, $p[0]['plan_id'],$id_bill)) return "No se pudo actualizar el estado de las prestaciones de la obra social que se trato de facturar";


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
        $this->db->select('B.benefit_id,B.medical_insurance_id, B.plan_id, B.period, 
                           SUM(B.value_honorary) AS total_honorary, SUM(B.value_expenses) AS total_expenses, count(B.benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits B');
        $this->db->join('professionals PF', 'B.id_professional_data = PF.id_professional_data');
        $this->db->join('fiscal_data PFD', 'PFD.id_fiscal_data = PF.id_fiscal_data');
        $this->db->where('B.medical_insurance_id', $id_medical);
        $this->db->where('B.state', 1);
        $this->db->where('B.period <=', $this->date_billing);

        //If medical insurance is OSDE (17), filter benefits depending on the bill's document type
        if($id_medical == 17 && $this->document_type == "F"){
            $this->db->where('PFD.iva_id', 6);  //Iva = monotributista
        }else{
            $this->db->where('PFD.iva_id <>', 6);  //Otros tipos de iva
        }

        $this->db->order_by("B.period", "asc");
        $this->db->order_by("B.plan_id", "asc");
        $this->db->group_by(array("B.period", "B.plan_id"));
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
        $this->db->select('B.benefit_id, B.plan_id, B.period, B.value_honorary, B.value_expenses, B.value_unit,
           SUM(B.value_honorary) AS total_honorary, SUM(B.value_expenses) AS total_expenses, count(B.benefit_id) as total_benefit', FALSE);
        $this->db->from('benefits B');
        $this->db->join('professionals PF', 'B.id_professional_data = PF.id_professional_data');
        $this->db->join('fiscal_data PFD', 'PFD.id_fiscal_data = PF.id_fiscal_data');
        $this->db->where('B.medical_insurance_id', $id_medical);
        $this->db->where('B.state', ($this->document_type == 'L')? 2:1);
        $this->db->where('B.period <=', $this->date_billing);

        //If medical insurance is OSDE (17), filter benefits depending on the bill's document type
        if($id_medical == 17 && $this->document_type == "F"){
            $this->db->where('PFD.iva_id', 6);  //Iva = monotributista
        }else{
            $this->db->where('PFD.iva_id <>', 6);  //Otros tipos de iva
        }

        $this->db->order_by("B.plan_id", "asc");
        $this->db->order_by("B.period", "asc");
        $this->db->group_by(array("B.plan_id", "B.period"));
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
        $this->db->where('period <=', $this->date_billing);
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
    private function updateBenefitsWithOneBillNumber($bill_number,$medical_insurance_id, $billID){

        $data = array(
            'B.state'       => 2,
            'B.bill_number' => $bill_number,
            'B.id_bill'     => $billID
        );

        $this->db->where('B.medical_insurance_id', $medical_insurance_id);
        $this->db->where('B.state', 1);
        $this->db->where('B.period <=', $this->date_billing);

        //If medical insurance is OSDE (17), filter benefits depending on the bill's document type
        if($medical_insurance_id == 17 && $this->document_type == "F"){
            $this->db->where('PFD.iva_id', 6);  //Iva = monotributista
        }else{
            $this->db->where('PFD.iva_id <>', 6);  //Otros tipos de iva
        }

        $this->db->update('benefits B join professionals PF on B.id_professional_data = PF.id_professional_data join fiscal_data PFD on PFD.id_fiscal_data = PF.id_fiscal_data', $data);

        if ($this->db->affected_rows() == 0) return false;

        return true;
    }

    //Update a benefit of a certain medical insurance and plan
    private function updateBenefitsWithManyBillNumber($bill_number,$medical_insurance_id,$plan_id,$billID){

        $data = array(
            'B.state'       => 2,
            'B.bill_number' => $bill_number,
            'B.id_bill'     => $billID
        );

        $this->db->where('B.medical_insurance_id', $medical_insurance_id);
        $this->db->where('B.plan_id', $plan_id);
        $this->db->where('B.state', 1);
        $this->db->where('B.period <=', $this->date_billing);

        //If medical insurance is OSDE (17), filter benefits depending on the bill's document type
        if($medical_insurance_id == 17 && $this->document_type == "F"){
            $this->db->where('PFD.iva_id', 6);  //Iva = monotributista
        }else{
            $this->db->where('PFD.iva_id <>', 6);  //Otros tipos de iva
        }

        $this->db->update('benefits B join professionals PF on B.id_professional_data = PF.id_professional_data join fiscal_data PFD on PFD.id_fiscal_data = PF.id_fiscal_data', $data);

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


        //Get medical insurance information
        $this->db->select('mi.denomination');
        $this->db->from('medical_insurance as mi');
        $this->db->where('mi.medical_insurance_id', $billData ['generalInformation']['id_medical_insurance']);
        $query =  $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudo obtener el nombre de la obra social para la factura'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontró nombre de obra social para la factura'];

        $billData ['generalInformation']['medical_insurance_denomination'] = $query->row()->denomination;


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

            $this->db->select('P.description');
            $this->db->from('plans as P');
            $this->db->where('P.plan_id', $billData ['body']['0']['plan_id']);
            $query =  $this->db->get();

            if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudo obtener el nombre del plan de la obra social a facturar'];
            if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontró el nombre del plan de la obra social a facturar'];

            $billData ['generalInformation']['plan_description'] = $query->row()->description;

        }else{

            $this->db->select('sum(total_honorary_period) as total_honorary_period,
	                           sum(total_expenses_period) as total_expenses_period,
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
            $billData ['generalInformation']['plan_description'] = "";

        }

        //Calculate total benefits of the bill
        $totalBillBenefit = 0;
        foreach ($billData ['body'] as $planOrPeriod){
            $totalBillBenefit = $totalBillBenefit + $planOrPeriod['total_benefit'];
        }
        $billData['generalInformation']['total_bill_benefits'] = $totalBillBenefit;

        //Create the receipt number (bill number with no zeros)
        $billData['generalInformation']['receipt_number'] = $billData['generalInformation']['number_bill'];

        //Convert the total number into letters
        $billData['generalInformation']['letter_total'] = $this->numbertoletter->to_word($billData['generalInformation']['total'],'ARS');

        //Complete bill number with zeros (8 zeros)
        $billData['generalInformation']['number_bill'] = str_pad($billData['generalInformation']['number_bill'], 8, '0', STR_PAD_LEFT);

        //Complete branch office with zeros (4 zeros)
        $billData['generalInformation']['branch_office'] = str_pad($billData['generalInformation']['branch_office'], 4, '0', STR_PAD_LEFT);

        //Complete cuit with zeros to fill 11 numbers. Then, add a -
        $billData['header']['cuit'] = str_pad($billData['header']['cuit'], 11, '0', STR_PAD_LEFT);
        $billData['header']['cuit'] = substr($billData['header']['cuit'], 0, 2) . '-' . substr($billData['header']['cuit'], 2);
        $billData['header']['cuit'] = substr($billData['header']['cuit'], 0, 11) . '-' . substr($billData['header']['cuit'], 11);


        return ['status' => 'ok', 'msg' => $billData];

    }

    //Get bills
    public function getBills(){

        $this->db->select('B.id_bill,B.branch_office,B.type_document,B.type_form,B.number_bill,B.id_medical_insurance,MI.denomination as medical_insurance_denomination,B.type_bill,B.date_billing,B.date_due,B.total,B.state_billing,B.amount_paid');
        $this->db->from('bill B');
        $this->db->join('medical_insurance MI','B.id_medical_insurance = MI.medical_insurance_id');
        $this->db->where('B.annulled', 0);
        $this->db->order_by("B.branch_office", "asc");
        $this->db->order_by("B.type_document", "asc");
        $this->db->order_by("B.type_form", "asc");
        $this->db->order_by("B.number_bill", "desc");
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];
        
        $bills = $query->result_array();

        //Get every bill's plan description
        foreach ($bills as &$bill){

            $this->db->select('P.description as plan_description');
            $this->db->from('bill_details_grouped BDG');
            $this->db->join('plans P','BDG.plan_id = P.plan_id');
            $this->db->where('BDG.id_bill', $bill['id_bill']);
            $this->db->limit(1);
            $query = $this->db->get();

            if (!$query)                 return [];
            if ($query->num_rows() == 0) return [];

            $bill['plan_description'] = $query->row()->plan_description;

        }


        foreach ($bills as &$bill) {

            //Don't show plans if the bill was billed by medical insurance
            if($bill['type_bill'] == 1) $bill['plan_description'] = "";

            //Change state
            switch ($bill['state_billing']) {
                case 1:
                    $bill['state_billing'] = 'Cargada';
                    break;
                case 2:
                    $bill['state_billing'] = 'Cobrada';
                    break;
                case 3:
                    $bill['state_billing'] = 'Facturada';
                    break;
                default:
                    $bill['state_billing'] = 'Desconocida';
            }

            //Change document type
            switch ($bill['type_document']) {
                case 'F':
                    $bill['type_document'] = 'Factura';
                    break;
                case 'L':
                    $bill['type_document']= 'Factura Ficticia';
                    break;
                default:
                    $bill['type_document'] = 'Desconocida';
            }

        }

        return $bills;

    }

    //Cancel bill
    public function cancelBill($billID){

        //Start transaction
        $this->db->trans_start();

            //Obtain the bill data
            $this->db->select('B.*');
            $this->db->from('bill B');
            $this->db->where('B.id_bill',$billID);
            $query = $this->db->get();

            if (!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar la factura que se quiere anular'];
            if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró la factura que se quiere anular'];

            $bill = $query->row();


            //Return bill's benefits to "valorized" (state = 1)
            $data = array(
                'state' => 1,
                'bill_number' => null,
                'id_bill' => null
            );

            $this->db->where('id_bill', $billID);
            $this->db->where('state', 2);
            $query = $this->db->update('benefits', $data);

            if (!$query) return ['status' => 'error', 'msg' => 'No se pudo actualizar el estado de las prestaciones de la factura a "valorizadas"'];
            if ($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudo actualizar el estado de las prestaciones de la factura a "valorizadas"'];


            //Check bill's state. If state = 2 it was billed, so we have to delete it's pay receipt (1-Cargada/Generada o 2-Cobrada)
            if ($bill->state_billing == 2) {
                //TODO: anular el remito de la factura
            }


            //Cancel the bill
            $this->db->where('id_bill', $billID);
            $this->db->update('bill', ['annulled' => 1]);

            if ($this->db->affected_rows() == 0)    return ['status' => 'error', 'msg' => 'No se pudo anular la factura'];

        //Close transaction
        $this->db->trans_complete();

        return ['status' => 'ok', 'msg' => 'Factura anulada con éxito'];

    }

    //Pay bill
    public function payBill($amount_paid,$pay_date){

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


















