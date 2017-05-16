<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');

class Receipt extends CI_Model{

    private $header;
    private $receipt_number;

    public function __construct(){
        parent::__construct();
        $this->load->library('numbertoletter');
        $this->load->model('Bill');
    }

    public function generateReceipt($billID){

        //First, check the bill is not annuled
        $this->db->select('B.*');
        $this->db->from('bill B');
        $this->db->where('B.id_bill', $billID);
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar la factura de la que se desea realizar el remito'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron los datos de la factura de la que se desea imprimir el remito'];

        if($query->row()->annulled == 1) return ['status' => 'error', 'msg' => 'No se puede imprimir el remito de esta factura ya que ha sido anulada'];

        //To print the receipt, we need the bill's print data and we have to add the professional information
        $receiptData = $this->Bill->getPrintData($billID)['msg'];

        $this->db->select('B.period,B.quantity,B.value_honorary,B.value_expenses,B.value_unit,PL.description,PR.registration_number,PR.name,FD.cuit,FD.iibb,N.code,N.class');
        $this->db->from('benefits B');
        $this->db->join('plans PL',             'PL.plan_id = B.plan_id');
        $this->db->join('professionals PR',     'PR.id_professional_data = B.id_professional_data');
        $this->db->join('fiscal_data FD',       'FD.id_fiscal_data = PR.id_fiscal_data');
        $this->db->join('nomenclators N',       'N.nomenclator_id = B.nomenclator_id');
        $this->db->where('B.bill_number', $receiptData['generalInformation']['receipt_number']);   //Receipt number = bill number without zeros
        $this->db->where('B.state', 2);
        $this->db->order_by("B.period", "asc");
        $this->db->order_by("PR.registration_number", "asc");
        $this->db->order_by("N.code", "asc");
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudieron obtener datos de la factura para realizar el remito'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron datos de la factura para realizar el remito'];

        $result = $query->result_array();

        //Group by periods
        $result = $this->array_group_by($result, 'period');

        //For every period, group by professional
        foreach ($result as &$periodArray){
            $periodArray = $this->array_group_by($periodArray, 'registration_number');
        }

        //Declare variables to show at the end of the receipt
        $totalReceiptData = [];
        $totalReceiptData['visitQuantity']        = 0;
        $totalReceiptData['surgeryQuantity']      = 0;
        $totalReceiptData['practiceQuantity']     = 0;
        $totalReceiptData['quantityTotalReceipt'] = 0;
        $totalReceiptData['honoraryTotalReceipt'] = 0;
        $totalReceiptData['expensesTotalReceipt'] = 0;
        $totalReceiptData['visitTotalReceipt']    = 0;
        $totalReceiptData['generalTotalReceipt']  = 0;

        //Calculate necessary fields
        foreach ($result as &$periodArray){

            foreach ($periodArray as &$professionalArray){

                foreach ($professionalArray as &$benefitArray) {

                    //If the benefit is V (visit) visit value is honorary value and honorary goes 0
                    if ($benefitArray['value_unit'] == 'V'){
                        $benefitArray['value_visit'] = $benefitArray['value_honorary'];
                        $benefitArray['value_honorary'] = 0;
                        $totalReceiptData['visitQuantity'] = $totalReceiptData['visitQuantity'] + $benefitArray['quantity'];
                    }else if ($benefitArray['value_unit'] == 'Q'){
                        $benefitArray['value_visit'] = 0;
                        $totalReceiptData['surgeryQuantity'] = $totalReceiptData['surgeryQuantity'] + $benefitArray['quantity'];
                    }else{
                        $benefitArray['value_visit'] = 0;
                        $totalReceiptData['practiceQuantity'] = $totalReceiptData['practiceQuantity'] + $benefitArray['quantity'];
                    }

                    //Calculate total for visits, honoraries and expenses
                    $benefitArray['visit_benefit_total']    = $benefitArray['value_visit']    * $benefitArray['quantity'];
                    $benefitArray['honorary_benefit_total'] = $benefitArray['value_honorary'] * $benefitArray['quantity'];
                    $benefitArray['expenses_benefit_total'] = $benefitArray['value_expenses'] * $benefitArray['quantity'];

                    //Calculate the total for each benefit
                    $benefitArray['benefit_total'] = $benefitArray['visit_benefit_total'] + $benefitArray['honorary_benefit_total'] + $benefitArray['expenses_benefit_total'];

                    //Will be filled later for the first benefit. Total for professional
                    $benefitArray['professional_quantity_total'] = 0;
                    $benefitArray['professional_visit_total']    = 0;
                    $benefitArray['professional_honorary_total'] = 0;
                    $benefitArray['professional_expenses_total'] = 0;
                    $benefitArray['professional_total']          = 0;

                    //Complete cuit with zeros to fill 11 numbers. Then, add a -
                    $benefitArray['cuit'] = str_pad($benefitArray['cuit'], 11, '0', STR_PAD_LEFT);
                    $benefitArray['cuit'] = substr($benefitArray['cuit'], 0, 2) . '-' . substr($benefitArray['cuit'], 2);
                    $benefitArray['cuit'] = substr($benefitArray['cuit'], 0, 11) . '-' . substr($benefitArray['cuit'], 11);

                }

                //Obtain the total values to fill the total row of each professional in the receipt
                $quantity_total     = 0;
                $visit_total        = 0;
                $honorary_total     = 0;
                $expenses_total     = 0;
                $professional_total = 0;

                foreach ($professionalArray as $benefitArray) {
                    $quantity_total     = $quantity_total     + $benefitArray['quantity'];
                    $visit_total        = $visit_total        + $benefitArray['visit_benefit_total'];
                    $honorary_total     = $honorary_total     + $benefitArray['honorary_benefit_total'];
                    $expenses_total     = $expenses_total     + $benefitArray['expenses_benefit_total'];
                    $professional_total = $professional_total + $benefitArray['benefit_total'];
                }

                //Fill the first benefit with the totals for the professional
                $professionalArray[0]['professional_quantity_total']    = $quantity_total;
                $professionalArray[0]['professional_visit_total']       = $visit_total;
                $professionalArray[0]['professional_honorary_total']    = $honorary_total;
                $professionalArray[0]['professional_expenses_total']    = $expenses_total;
                $professionalArray[0]['professional_total']             = $professional_total;

            }

            $periodTotal = [];
            $periodTotal['period_benefit_quantity'] = 0;
            $periodTotal['period_visit_total']      = 0;
            $periodTotal['period_honorary_total']   = 0;
            $periodTotal['period_expenses_total']   = 0;
            $periodTotal['period_total']            = 0;

            //For each professional, get it's totals (first benefit of the professional has the total value for that professional)
            foreach ($periodArray as &$professionalArray) {
                $periodTotal['period_benefit_quantity'] = $periodTotal['period_benefit_quantity'] + $professionalArray[0]['professional_quantity_total'];
                $periodTotal['period_visit_total']      = $periodTotal['period_visit_total']      + $professionalArray[0]['professional_visit_total'];
                $periodTotal['period_honorary_total']   = $periodTotal['period_honorary_total']   + $professionalArray[0]['professional_honorary_total'];
                $periodTotal['period_expenses_total']   = $periodTotal['period_expenses_total']   + $professionalArray[0]['professional_expenses_total'];
                $periodTotal['period_total']            = $periodTotal['period_total']            + $professionalArray[0]['professional_total'];
            }

            $totalReceiptData['quantityTotalReceipt'] = $totalReceiptData['quantityTotalReceipt'] + $periodTotal['period_benefit_quantity'];
            $totalReceiptData['generalTotalReceipt']  = $totalReceiptData['generalTotalReceipt']  + $periodTotal['period_total'];
            $totalReceiptData['honoraryTotalReceipt'] = $totalReceiptData['honoraryTotalReceipt'] + $periodTotal['period_honorary_total'];
            $totalReceiptData['expensesTotalReceipt'] = $totalReceiptData['expensesTotalReceipt'] + $periodTotal['period_expenses_total'];
            $totalReceiptData['visitTotalReceipt']    = $totalReceiptData['visitTotalReceipt']    + $periodTotal['period_visit_total'];

            $periodArray [] = $periodTotal;

        }

        $returnArray = [];
        $returnArray [] = $result;
        $returnArray [] = $totalReceiptData;


        print_r($returnArray);die();

        //print_r($this->db->error());die();




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















