<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');


class CreditDebitNote extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->library('numbertoletter');
        $this->load->model('Bill');
    }

    private $credit_debit_type;

    public function createNote($medical_insurance_id, $id_bill, $document_type,$branch_office,$form_type){

        //Obtain the bill data
        $this->db->select('B.*');
        $this->db->from('bill B');
        $this->db->where('B.id_bill', $id_bill);
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudieron obtener los datos de la factura asociada a la nota'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron los datos de la factura asociada a la nota'];

        $billData = $query->row();


        //If the bill's state is 2 (Cobrada parcial) or 1 (Generada) the state of the note will be 1 (Generada) (Se liquidará en el proximo pago)
        //If the bill's state is 3 (Cobrada total) the state of the note will be 2 (Pendiente a liquidar) (Se liquidara cuando se haga la liquidacion)
        if($billData->state_billing == 1 || $billData->state_billing == 2){
            $noteState = 1;
        }elseif ($billData->state_billing == 3 || $billData->state_billing == 4){
            $noteState = 2;
        }else{
            $noteState = 1;
        }


        //Obtain the credit-debit type. It's the opposite of the document type
        $this->credit_debit_type = ($document_type == 'C') ? 'D' : 'C';


        //Obtain note number
        $noteNumber = $this->calculateNoteNumber($id_bill,$document_type);
        if(empty($noteNumber)) return ['status' => 'error', 'msg' => 'No se pudo obtener el número de la nota a crear'];


        //Obtain the sum of the credits or debits of the note
        $creditDebitData = $this->getCreditDebitsOfNote($id_bill,$document_type);
        if(empty($creditDebitData)) return ['status' => 'error', 'msg' => 'No existen créditos/débitos asociados a la nota'];

        $totalHonoraries = 0;
        $totalExpenses   = 0;
        $totalNote       = 0;

        foreach ($creditDebitData as $creditDebit){
            $totalHonoraries = $totalHonoraries + ($creditDebit['value_honorary'] * $creditDebit['quantity']);
            $totalExpenses   = $totalExpenses   + ($creditDebit['value_expenses'] * $creditDebit['quantity']);
            $totalNote       = $totalNote       + (($creditDebit['value_honorary'] + $creditDebit['value_expenses']) * $creditDebit['quantity']);
        }

        $now = date('Y-m-d H:i:s');

        $data = array(
            'id_bill'                   => $id_bill,
            'medical_insurance_id'      => $medical_insurance_id,
            'document_type'             => $document_type,
            'branch_office'             => $branch_office,
            'type_form'                 => $form_type,
            'creation_date'             => $now,
            'expiration_date'           => $now,
            'credit_debit_note_number'  => $noteNumber,
            'state'                     => $noteState,
            'total_expenses'            => $totalExpenses,
            'total_honoraries'          => $totalHonoraries,
            'total_note'                => $totalNote,
            'annulled'                  => 0
        );

        //Start transaction
        $this->db->trans_start();

            //Save the note
            $this->db->insert('credit_debit_note', $data);
            if($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudo grabar la nota de crédito/débito: error al grabar la nota'];

            //Obtain last inserted note id
            $noteID = $this->db->insert_id();

            //Update each of the note's credit or debit
            $this->db->where(['id_bill' => $id_bill, 'type' =>$this->credit_debit_type]);
            $this->db->update('credit_debit', ['credit_debit_note_id' =>$noteID ]);
            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo grabar la nota de crédito/débito: error al asociar créditos/débitos a la nota'];

        //End transaction and check everything is ok
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) return ['status' => 'error', 'msg' => 'Error inesperado al generar la nota de débito/crédito'];

        return ['status' => 'ok', 'msg' => 'Nota de crédito/débito creada satisfactoriamente'];

    }

    private function calculateNoteNumber($id_bill,$document_type){

        //Get bill's data
        $this->db->select('B.*');
        $this->db->from('bill B');
        $this->db->where('B.id_bill', $id_bill);
        $query = $this->db->get();

        if(!$query)                 return 0;
        if($query->num_rows() <= 0) return 0;

        $billData = $query->row();

        //Get the note number
        $this->db->select_max('CDN.credit_debit_note_number');
        $this->db->from('credit_debit_note CDN');
        $this->db->where('branch_office', $billData->branch_office);
        $this->db->where('document_type', $document_type);
        $this->db->where('type_form', $billData->type_form);
        $query = $this->db->get();

        if(!$query) return 0;

        $previousNoteNumber = $query->row()->credit_debit_note_number;

        if (empty($previousNoteNumber)) $previousNoteNumber = 0;

        // Add 1 to the number obtained so we get the next bill number
        $previousNoteNumber ++;

        return $previousNoteNumber;


    }

    private function getCreditDebitsOfNote($id_bill,$document_type){

        //Change the document type to opposite (credit notes have debits, debit notes have credts)
        $credit_debit_type = ($document_type == 'C') ? 'D' : 'C';

        //Get credits or debits of a note
        $this->db->select('CD.*');
        $this->db->from('credit_debit CD');
        $this->db->where('CD.id_bill', $id_bill);
        $this->db->where('CD.type', $credit_debit_type);
        $this->db->where('CD.credit_debit_note_id', null);
        $query = $this->db->get();

        if(!$query)                 return 0;
        if($query->num_rows() <= 0) return 0;

        return $query->result_array();

    }

    public function getNotes(){

        $this->db->select('CDN.*,MI.denomination');
        $this->db->from('credit_debit_note CDN');
        $this->db->join('medical_insurance MI', 'MI.medical_insurance_id=CDN.medical_insurance_id');
        $this->db->order_by("CDN.branch_office", "asc");
        $this->db->order_by("CDN.type_form", "asc");
        $this->db->order_by("CDN.credit_debit_note_number", "desc");
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        $notes = $query->result_array();

        foreach ($notes as &$note) {

            //Change state
            switch ($note['state']) {
                case 1:
                    $note['note_state'] = 'Generada';
                    break;
                case 2:
                    $note['note_state'] = 'Pendiente de liquidación';
                    break;
                default:
                    $note['note_state'] = 'Desconocida';
            }

        }

        return $notes;

    }

    public function getNotesForBill($bill_id){

        $this->db->select('CDN.*');
        $this->db->from('credit_debit_note CDN');
        $this->db->where('CDN.id_bill', $bill_id);

        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Ocurrió un error en el chequeo de notas de crédito/débito de la factura'];
        if ($query->num_rows() == 0) return ['status' => 'ok', 'msg' => [] ];

        return ['status' => 'ok', 'msg' => $query->result_array()];

    }

    public function annulate($credit_debit_note_id){

        //Get the note to annulate
        $this->db->select('CDN.*');
        $this->db->from('credit_debit_note CDN');
        $this->db->where('CDN.credit_debit_note_id',$credit_debit_note_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar la nota que se quiere anular'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró la nota que se quiere anular'];

        $note = $query->row();


        //Check the note can be cancelled (not cancelled yet or invalid state)
        if($note->state == 3 || $note->annulled == 1) return ['status' => 'error', 'msg' => 'No se puede anular la nota ya que se encuentra en estado Liquidada o bien ya ha sido anulada'];

        //Start transaction
        $this->db->trans_start();

            //Annulate the note
            $this->db->where('credit_debit_note_id', $credit_debit_note_id);
            $this->db->update('credit_debit_note', ['annulled' => 1,'pay_receipt_id' => null]);

            if($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudo anular la nota de crédito/débito'];


            //Delete all it's credit's or debits
            $this->db->delete('credit_debit', ['credit_debit_note_id' =>$credit_debit_note_id]);

            if($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudieron eliminar los créditos/débitos asociados a la nota'];


        //End transaction and check everything is ok
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) return ['status' => 'error', 'msg' => 'Error inesperado al eliminar la nota y/o sus débitos/créditos'];

        return ['status' => 'ok', 'msg' => 'Nota eliminada satisfactoriamente'];
    }

    public function getPrintData($credit_debit_note_id){

        $this->db->select('CDN.*,MI.settlement_name, MI.address, MI.location, MI.postal_code, MI.iva_id , MI.cuit, ides.description as iva_description,B.type_document as bill_document_type,B.number_bill');
        $this->db->from('credit_debit_note CDN');
        $this->db->join('medical_insurance MI', 'CDN.medical_insurance_id = MI.medical_insurance_id');
        $this->db->join('iva ides','MI.iva_id = ides.iva_id');
        $this->db->join('bill B','B.id_bill = CDN.id_bill');
        $this->db->where("CDN.credit_debit_note_id",$credit_debit_note_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error al obtener los datos de la nota de crédito/débito'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudieron obtener datos de la nota elegida'];

        $notePrintData = $query->result_array()[0];

        //Format cuit
        $notePrintData['cuit'] = str_pad($notePrintData['cuit'], 11, '0', STR_PAD_LEFT);
        $notePrintData['cuit'] = substr($notePrintData['cuit'], 0, 2) . '-' . substr($notePrintData['cuit'], 2);
        $notePrintData['cuit'] = substr($notePrintData['cuit'], 0, 11) . '-' . substr($notePrintData['cuit'], 11);

        $notePrintData['letterTotal'] = $this->numbertoletter->to_word(floor($notePrintData['total_note']),'ARS');

        return ['status' => 'ok', 'msg' => $notePrintData];


    }

    public function generateReceipt($noteID){

        //First, check the note is not annuled
        $this->db->select('CDN.*');
        $this->db->from('credit_debit_note CDN');
        $this->db->where('CDN.credit_debit_note_id', $noteID);
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar la nota de la que se desea realizar el remito'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron los datos de la nota de la que se desea imprimir el remito'];

        $noteData = $query->row();

        if($noteData->annulled == 1) return ['status' => 'error', 'msg' => 'No se puede imprimir el remito de esta nota ya que ha sido anulada'];

        //Obtain the bill id
        $billID = $noteData->id_bill;

        //To print the receipt, we need the bill's print data and we have to add the professional information
        $this->db->select('CD.period,CD.quantity,CD.value_honorary,CD.value_expenses,N.unity as value_unit,PR.registration_number,PR.name,FD.cuit,FD.iibb,N.code,N.class');
        $this->db->from('credit_debit_note CDN');
        $this->db->join('credit_debit CD',      'CD.credit_debit_note_id = CDN.credit_debit_note_id');
        $this->db->join('professionals PR',     'PR.id_professional_data = CD.id_professional_data');
        $this->db->join('fiscal_data FD',       'FD.id_fiscal_data = PR.id_fiscal_data');
        $this->db->join('nomenclators N',       'N.nomenclator_id = CD.nomenclator_id');
        $this->db->where('CDN.id_bill', $billID);
        $this->db->order_by("CD.period", "asc");
        $this->db->order_by("PR.registration_number", "asc");
        $this->db->order_by("N.code", "asc");
        $query = $this->db->get();

        if(!$query)                 return ['status' => 'error', 'msg' => 'No se pudieron obtener datos de la nota para realizar el remito'];
        if($query->num_rows() <= 0) return ['status' => 'error', 'msg' => 'No se encontraron datos de la nota para realizar el remito'];

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
                    //$benefitArray['cuit'] = str_pad($benefitArray['cuit'], 11, '0', STR_PAD_LEFT);
                    //$benefitArray['cuit'] = substr($benefitArray['cuit'], 0, 2) . '-' . substr($benefitArray['cuit'], 2);
                    //$benefitArray['cuit'] = substr($benefitArray['cuit'], 0, 11) . '-' . substr($benefitArray['cuit'], 11);

                }

                //Obtain the total values to fill the total row of each professional in the receipt
                $quantity_total     = 0;
                $visit_total        = 0;
                $honorary_total     = 0;
                $expenses_total     = 0;
                $professional_total = 0;

                foreach ($professionalArray as &$benefitArray) {

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
        $returnArray ['body'] = $result;
        $returnArray ['endData'] = $totalReceiptData;

        //Get receipt first page data (same page as bill)
        $billHeadData = $this->Bill->getPrintData($billID);
        if($billHeadData['status'] == 'error') return ['status' => 'error', 'msg' => 'No se pudieron obtener los datos de la cabecera del remito'];

        //Add note data
        $billHeadData['msg']['generalInformation']['noteNumber']   = $noteData->credit_debit_note_number;
        $billHeadData['msg']['generalInformation']['noteType']     = ($noteData->document_type == 'C')? 'Crédito' : 'Débito';
        $billHeadData['msg']['generalInformation']['total']        = $noteData->total_note;
        $billHeadData['msg']['generalInformation']['letter_total'] = $this->numbertoletter->to_word(floor($noteData->total_note),'ARS');

        $returnArray ['firstPage'] = $billHeadData['msg'];

        return ['status' => 'ok', 'msg' => $returnArray];

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
