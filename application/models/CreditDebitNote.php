<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');


class CreditDebitNote extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->library('numbertoletter');
    }

    private $table = "credit_debit_note";


    public function createNote($medical_insurance_id, $id_bill, $document_type,$branch_office,$form_type){

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

        $data = [
            'id_bill'                   => $id_bill,
            'medical_insurance_id'      => $medical_insurance_id,
            'document_type'             => $document_type,
            'branch_office'             => $branch_office,
            'type_form'                 => $form_type,
            'creation_date'             => date('Y-m-d H:i:s'),
            'expiration_date'           => date('Y-m-d H:i:s'),
            'credit_debit_note_number'  => $noteNumber,
            'state'                     => 1,
            'total_expenses'            => $totalExpenses,
            'total_honoraries'          => $totalHonoraries,
            'total_note'                => $totalNote
        ];

        //Start transaction
        $this->db->trans_start();

            //Save the note
            $this->db->insert($this->table, $data);
            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo grabar la nota de crédito/débito: error al guardar la nota'];

            $noteID = $this->db->insert_id();

            //Update each of the note's credit or debit
            $this->db->where(['id_bill' => $id_bill, 'type' =>$document_type]);
            $this->db->update('credit_debit', ['credit_debit_note_id' =>$noteID ]);
            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo grabar la nota de crédito/débito: error al asociar créditos/débitos a la nota'];

        //End transaction and check everything is ok
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) return ['status' => 'error', 'msg' => 'Error al eliminar la nota y/o sus débitos/créditos'];


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

        //Get credits or debits of a note
        $this->db->select('CD.*');
        $this->db->from('credit_debit CD');
        $this->db->where('CD.id_bill', $id_bill);
        $this->db->where('CD.type', $document_type);
        $query = $this->db->get();

        if(!$query)                 return 0;
        if($query->num_rows() <= 0) return 0;

        return $query->result_array();

    }

    public function getNotes(){

        $this->db->select('CDN.*');
        $this->db->from('credit_debit_note CDN');
        $this->db->order_by("CDN.branch_office", "asc");
        $this->db->order_by("CDN.type_form", "asc");
        $this->db->order_by("CDN.credit_debit_note_number", "desc");
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        return $query->result_array();

    }
    
    public function annulate($credit_debit_note_id){

        //Start transaction
        $this->db->trans_start();

            //Annulate the note
            $this->db->where('credit_debit_note_id', $credit_debit_note_id);
            $this->db->update('$credit_debit_note', ['annulled'=>1]);

            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo grabar la nota de crédito/débito'];


            //Delete all it's credit's or debits
            $this->db->delete('credit_debit', ['credit_debit_note_id' =>$credit_debit_note_id]);

            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo grabar la nota de crédito/débito'];


        //End transaction and check everything is ok
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) return ['status' => 'error', 'msg' => 'Error al eliminar la nota y/o sus débitos/créditos'];

        return ['status' => 'ok', 'msg' => 'Nota eliminada satisfactoriamente'];
    }

    public function getPrintData($credit_debit_note_id){

        $this->db->select('CDN.*,MI.settlement_name, MI.address, MI.location, MI.postal_code, MI.iva_id , MI.cuit, ides.description as iva_description,B.type_document as bill_document_type');
        $this->db->from('credit_debit_note CDN');
        $this->db->join('medical_insurance MI', 'CDN.medical_insurance_id = MI.medical_insurance_id');
        $this->db->join('iva ides','MI.iva_id = ides.iva_id');
        $this->db->join('bill B','B.id_bill = CDN.id_bill');
        $this->db->where("CDN.credit_debit_note_id",$credit_debit_note_id);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error al obtener los datos de la nota de crédito/débito'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudieron obtener datos de la nota elegida'];

        $notePrintData = $query->result_array();

        $notePrintData['letterTotal'] = $this->numbertoletter->to_word(floor($notePrintData['total_note']),'ARS');

        return ['status' => 'ok', 'msg' => $notePrintData];

    }










}
