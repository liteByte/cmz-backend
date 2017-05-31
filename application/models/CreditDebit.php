<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class CreditDebitConcept extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    private $table = "credit_debit";

    //Creates the credit/debit in 'credit_debit'
    public function save($medical_insurance_id, $id_bill, $type, $id_professional_data, $period, $nomenclator_id, $value_honorary, $value_expenses, $quantity, $concept_id){
        
        $data = array(
            'medical_insurance_id'  => $medical_insurance_id,
            'id_bill'               => $id_bill,
            'type'                  => $type,
            'id_professional_data'  => $id_professional_data,
            'period'                => $period,
            'nomenclator_id'        => $nomenclator_id,
            'value_honorary'        => $value_honorary,
            'value_expenses'        => $value_expenses,
            'quantity'              => $quantity,
            'concept_id'            => $concept_id
        );

        $this->db->insert($this->table, $data);

        if($this->db->affected_rows() == 0) ['status' => 'error', 'msg' => 'No se pudo grabar el crédito/débito'];

        return ['status' => 'ok', 'msg' => 'Crédito/débito creado satisfactoriamente'];

    }

    //Updates the credit/debit in 'credit_debit'
    public function update($value_honorary, $value_expenses, $quantity, $concept_id, $credit_debit_id){

        $data = array(
            'value_honorary'        => $value_honorary,
            'value_expenses'        => $value_expenses,
            'quantity'              => $quantity,
            'concept_id'            => $concept_id
        );

        $this->db->where('credit_debit_id', $credit_debit_id);
        $this->db->update($this->table, $data);

        if ($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudo modificar el crédito/débito'];

        return ['status' => 'ok', 'msg' => 'Crédito/débito modificado satisfactoriamente'];

    }

    //Delete credit/debit
    public function delete($credit_debit_id){

        $this->db->delete($this->table, ['credit_debit_id' => $credit_debit_id]);

        if ($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudo eliminar el crédito/débito'];

        return ['status' => 'ok', 'msg' => 'Crédito/débito eliminado satisfactoriamente'];

    }

    //Delete all credits or debits associated to a bill
    public function deleteAll($id_bill,$type){

        $this->db->delete($this->table, ['id_bill' => $id_bill , 'type' => $type]);

        if ($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'No se pudieron eliminar los créditos/débitos'];

        return ['status' => 'ok', 'msg' => 'Créditos/débitos eliminados satisfactoriamente'];

    }

    //Validate existence of a benefit with a certain professional + period + nomenclator in a certain bill
    public function validateExistence($id_bill, $id_professional_data, $period, $nomenclator_id){

        $this->db->select('B.benefit_id');
        $this->db->from('benefits B');
        $this->db->where('B.id_professional_data',$id_professional_data);
        $this->db->where('B.period',$period);
        $this->db->where('B.nomenclator_id',$nomenclator_id);
        $this->db->where('B.id_bill',$id_bill);
        $this->db->where('B.active',"active");
        $query = $this->db->get();

        if (!$query) return ['status' => 'error', 'msg' => 'No se pudo validar la existencia de la prestación'];

        //If no benefits where found
        if ($query->num_rows() == 0) {
            return ['status' => 'ok', 'msg' => 'No'];
        }else{
            return ['status' => 'ok', 'msg' => 'Yes'];
        }

    }













}
