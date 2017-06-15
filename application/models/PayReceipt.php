<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class PayReceipt extends CI_Model{

    public function __construct(){
        parent::__construct();
        $this->load->model('Bill');
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

    public function cancelPayReceipt ($payReceiptID){

        //Get the receipt data
        $payReceiptToCancel = $this->getPayReceiptByID($payReceiptID)[0];
        if(empty($payReceiptToCancel)) return ['status' => 'error', 'msg' => 'No se encontró el recibo que se quiere anular'];


        //If the pay receipt was liquidated, it cannot be anulled
        if($payReceiptToCancel['liquidated'] == 1) return ['status' => 'error', 'msg' => 'No se puede anular este recibo debido a que ya ha sido liquidado'];


        //Get the bill data of the receipt
        $this->db->select('B.*');
        $this->db->from('bill B');
        $this->db->where('B.id_bill',$payReceiptToCancel['id_bill']);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar la factura asociada al recibo'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró la factura asociada al recibo'];

        $bill = $query->row();


        //Check the number of pay receipts left (not liquidated)
        $this->db->select('count(*) as not_liquidated_receipts');
        $this->db->from('pay_receipt PR');
        $this->db->where('PR.id_bill',$bill->id_bill);
        $this->db->where('PR.annulled',0);
        $this->db->where('PR.state',1);
        $query = $this->db->get();

        if (!$query) return ['status' => 'error', 'msg' => 'Error inesperado en el chequeo de recibos pendientes de pago de la factura'];

        $notLiquidatedReceiptsQuantity = $query->row()->not_liquidated_receipts;

        //Check the number of pay receipts left (liquidated)
        $this->db->select('count(*) as liquidated_receipts');
        $this->db->from('pay_receipt PR');
        $this->db->where('PR.id_bill',$bill->id_bill);
        $this->db->where('PR.annulled',0);
        $this->db->where('PR.state',2);
        $this->db->where('PR.liquidated',1);
        $query = $this->db->get();

        if (!$query) return ['status' => 'error', 'msg' => 'Error inesperado en el chequeo de recibos liquidados pendientes de la factura'];

        $liquidatedReceiptsQuantity = $query->row()->liquidated_receipts;

        //Start transaction
        $this->db->trans_start();

            //If the bill has no liquidated receipts and there is only one receipt (not payed), return bill state, it's credit-debit notes (all of them) and it's benefits to "1- Cargada/Generada"
            if($liquidatedReceiptsQuantity == 0 && $notLiquidatedReceiptsQuantity == 1){

                //Update bill
                $this->db->where('id_bill', $bill->id_bill);
                $this->db->update('bill', ['state_billing' => 1]);

                //Update all benefits of the fee
                $this->db->where('id_bill', $bill->id_bill);
                $this->db->update('benefits', ['state' => 2]);

                //Update credit-debit notes
                $this->db->where('id_bill', $bill->id_bill);
                $this->db->update('credit_debit_note', ['state' => 1]);

            //If the bill has liquidated receipts but there is only one receipt (not payed), return all bill's credit debit notes that are "2- Pendientes de liquidacion" to "1- Cargada/Generada"
            }else if($liquidatedReceiptsQuantity > 0 && $notLiquidatedReceiptsQuantity == 1){

                //Update not liquidated credit-debit notes
                $this->db->where('id_bill', $bill->id_bill);
                $this->db->where('state', 2);
                $this->db->update('credit_debit_note', ['state' => 1]);

            }

            //Annulate the receipt
            $this->db->where('pay_receipt_id', $payReceiptID);
            $this->db->update('pay_receipt', ['annulled' => 1]);

            //Check if the bill is now partially payed and update the amount payed
            if($bill->amount_paid - $payReceiptToCancel['amount_paid'] > 0){
                $this->db->where('id_bill', $bill->id_bill);
                $this->db->update('bill', ['state_billing' => 2, 'amount_paid' => ($bill->amount_paid - $payReceiptToCancel['amount_paid'])]);
            }else{
                $this->db->where('id_bill', $bill->id_bill);
                $this->db->update('bill', ['amount_paid' => ($bill->amount_paid - $payReceiptToCancel['amount_paid'])]);
            }

        //Close transaction
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) return ['status' => 'error', 'msg' => 'Error inesperado: no se pudo anular el recibo'];

        return ['status' => 'ok', 'msg' => 'Recibo anulado correctamente'];

    }

    public function getPayReceiptByID($payReceiptID){

        $this->db->select('PR.*');
        $this->db->from('pay_receipt PR');
        $this->db->where('PR.pay_receipt_id',$payReceiptID);
        $this->db->where('PR.annulled',0);
        $this->db->where('PR.state',1);
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        return $query->result_array();



    }

    public function getReceipts ($billID){

        //Obtain the bill's total
        $this->db->select('B.total');
        $this->db->from('bill B');
        $this->db->where('B.id_bill',$billID);
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar los datos de pago de la factura'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontraron los datos de pago de la factura'];

        $billTotal = $query->row()->total;

        //Obtain the total for the credit-debit notes of the bill
        $this->db->select('CDN.*');
        $this->db->from('credit_debit_note CDN');
        $this->db->where('CDN.id_bill',$billID);
        $this->db->where('CDN.annulled',0);
        $query = $this->db->get();

        if (!$query) return ['status' => 'error', 'msg' => 'Error al buscar notas de credito/débito asociadas a la factura'];

        $notes      = $query->result_array();
        $totalNotes = 0;

        foreach($notes as $note){
            if($note['document_type'] == 'C'){
                //Credit note -
                $totalNotes             = $totalNotes           - $note['total_note'];
            }else{
                //Debit note +
                $totalNotes             = $totalNotes           + $note['total_note'];
            }
        }


        //Calculate the pending total of the bill
        $pendingTotal = $billTotal + $totalNotes;

        //Obtain the receipts data
        $this->db->select('PR.*,MI.denomination,concat(B.type_document,\'-\',B.type_form,\'-\',lpad(convert(B.branch_office,char),3,\'0\'),\'-\',lpad(convert(B.number_bill,char),8,\'0\')) as bill_number');
        $this->db->from('pay_receipt PR');
        $this->db->join('medical_insurance MI', 'MI.medical_insurance_id = PR.id_medical_insurance');
        $this->db->join('bill B', 'B.id_bill = PR.id_bill');
        $this->db->where('PR.id_bill',$billID);

        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        $receipts = $query->result_array();

        foreach ($receipts as &$receipt) {

            if($receipt['liquidated'] == 1){
                $receipt['state'] = 'Liquidado';
            }else if($receipt['annulled'] == 1){
                $receipt['state'] = 'Anulado';
            }else{
                $receipt['state'] = 'Generado';
            }

            $receipt['pending_total'] = $pendingTotal;

        }

        return $receipts;

    }

    public function getPrintData ($payReceiptID){

        $this->db->select('PR.*,MI.denomination,concat(B.type_document,\'-\',B.type_form,\'-\',lpad(convert(B.branch_office,char),3,\'0\'),\'-\',lpad(convert(B.number_bill,char),8,\'0\')) as bill_number');
        $this->db->from('pay_receipt PR');
        $this->db->join('medical_insurance MI', 'MI.medical_insurance_id = PR.id_medical_insurance');
        $this->db->join('bill B', 'B.id_bill = PR.id_bill');
        $this->db->where('PR.pay_receipt_id',$payReceiptID);

        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error al buscar los datos del recibo a imprimir'];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontraron los datos del recibo a imprimir'];

        $receipt = $query->result_array()[0];

        return ['status' => 'ok', 'msg' => $receipt];

    }










}
