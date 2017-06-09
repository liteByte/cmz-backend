<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class PayReceipt extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    //Generate the correct pay receipt number based on Branch Office + Document Type + Document Form
    public function generatePayReceiptNumber ($branch_office,$form_type,$document_type ){

        $this->db->select_max('pay_receipt_number');
        $this->db->where('branch_office', $branch_office);
        $this->db->where('type_document', $document_type);
        $this->db->where('type_form', $form_type);
        $this->db->from('pay_receipt');
        $query = $this->db->get();

        $result = $query->row()->pay_receipt_number;

        if (empty($result)) $result = 0;

        // Add 1 to the number obtained so we get the next pay receipt number
        $result ++;

        return $result;

    }













}
