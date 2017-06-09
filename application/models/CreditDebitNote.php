<?php
date_default_timezone_set('America/Buenos_Aires');
defined('BASEPATH') OR exit('No direct script access allowed');


class CreditDebitNote extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->library('numbertoletter');
    }

    private $credit_debit_type;

    public function createNote($medical_insurance_id, $id_bill, $document_type,$branch_office,$form_type){

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
            'state'                     => 1,
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

        //Start transaction
        $this->db->trans_start();

            //Annulate the note
            $this->db->where('credit_debit_note_id', $credit_debit_note_id);
            $this->db->update('credit_debit_note', ['annulled' => 1]);

            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo anular la nota de crédito/débito'];


            //Delete all it's credit's or debits
            $this->db->delete('credit_debit', ['credit_debit_note_id' =>$credit_debit_note_id]);

            if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudieron eliminar los créditos/débitos asociados a la nota'];


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










}
