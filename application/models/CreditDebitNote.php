<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');


class CreditDebitNote extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    private $table = "credit_debit_note";


    public function createNote($medical_insurance_id, $id_bill, $document_type){

        //Obtain note number
        $noteNumber = $this->calculateNoteNumber($id_bill,$document_type);
        if(empty($noteNumber)) return ['status' => 'error', 'msg' => 'No se pudo obtener el número de la nota a crear'];


        //Obtain the sum of the credits or debits of the note
        $creditDebitData = $this->getCreditDebitsOfNote($id_bill,$document_type);
        if(empty($creditDebitData)) return ['status' => 'error', 'msg' => 'No existen créditos/débitos asociados a la nota'];

        $totalHonoraries = 0;
        $totalExpenses   = 0;
        $totalNote       = 0;

        /*foreach ($creditDebits as $creditDebit){
            $totalHonoraries = $totalHonoraries + ($creditDebit->value_honorary * $creditDebit->quantity);
            $totalExpenses   = $totalExpenses   + ($creditDebit->value_expenses * $creditDebit->quantity);
            $totalNote       = $totalNote       + (($creditDebit->value_honorary + $creditDebit->value_expenses) * $creditDebit->quantity);
        }*/














        $now = date('Y-m-d H:i:s');



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
        $this->db->where('type_document', $document_type);
        $this->db->where('type_form', $billData->form_type);
        $query = $this->db->get();

        if(!$query)                 return 0;
        if($query->num_rows() <= 0) return 0;

        $result = $query->row();

        if (empty($result)) $result = 0;

        // Add 1 to the number obtained so we get the next bill number
        $result ++;

        return $result;


    }

    private function getCreditDebitsOfNote($id_bill,$document_type){

        //Get credits or debits of a nota
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
        $this->db->where('type_document', $document_type);
        $this->db->where('type_form', $billData->form_type);
        $query = $this->db->get();

        if(!$query)                 return 0;
        if($query->num_rows() <= 0) return 0;

        $result = $query->row();

        if (empty($result)) $result = 0;

        // Add 1 to the number obtained so we get the next bill number
        $result ++;

        return $result;


    }









}
