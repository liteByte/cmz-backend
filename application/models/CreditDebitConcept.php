<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class CreditDebitConcept extends CI_Model{

    private $code;
    private $concept_description;
    private $concept_group_id;
    private $concept_type_id;
    private $concept_movement_id;
    private $value;
    private $applies_liquidation;
    private $receipt_legend;

  public function __construct(){
    parent::__construct();
  }

  //Creates the concept in 'credit_debit_concepts'
  public function save($code, $concept_description, $concept_group_id,$concept_type_id,$concept_movement_id,$value,$applies_liquidation,$receipt_legend){

    $data = array(
        'code'                      => $code,
        'concept_description'       => $concept_description,
        'concept_group_id'          => $concept_group_id,
        'concept_type_id'           => $concept_type_id,
        'concept_movement_id'       => $concept_movement_id,
        'value'                     => $value,
        'applies_liquidation'       => $applies_liquidation,
        'receipt_legend'            => $receipt_legend,
        'active'                    => 'active'
    );

    $this->db->insert('credit_debit_concepts', $data);

    return true;

  }

  //Updates the concept in 'credit_debit_concepts'
  public function update($concept_description, $concept_group_id, $concept_type_id, $concept_movement_id, $value, $applies_liquidation, $receipt_legend, $id, $userID){

    $now = date('Y-m-d H:i:s');

    $data = array(
        'concept_description'       => $concept_description,
        'concept_group_id'          => $concept_group_id,
        'concept_type_id'           => $concept_type_id,
        'concept_movement_id'       => $concept_movement_id,
        'value'                     => $value,
        'applies_liquidation'       => $applies_liquidation,
        'receipt_legend'            => $receipt_legend,
        'active'                    => 'active',
        'update_date'               => $now,
        'modify_user_id'            => $userID
    );

    $this->db->where('concept_id', $id);
    $this->db->update('credit_debit_concepts', $data);

    return true;

  }

  //Get all concepts
  public function getConcepts(){

    $result = array();

    $this->db->where(['active' => "active"]);
    $this->db->order_by("concept_description", "asc");
    $query = $this->db->get('credit_debit_concepts');

    foreach ($query->result_array('CreditDebitConcept') as $row){
      array_push($result,$row);
    }

    return $result;

  }

  //Get a specific concept information
  public function getConceptById($conceptID){

    $result = array();

    $query = $this->db->get_where('credit_debit_concepts', ["concept_id" => $conceptID]);

    return $query->row();
  }

  //Delete concept information in 'concepts'
  //TODO:El sistema valida el concepto a eliminar no esté asociado a alguna liquidación pasada o actual.
  public function delete($conceptID,$userID){

    $now = date('Y-m-d H:i:s');
    $query = $this->db->get_where('credit_debit_concepts', ["concept_id" => $conceptID]);

    if($query->num_rows()){
      //Delete insurance
      $this->db->where('concept_id', $conceptID);
      $result = $this->db->delete('credit_debit_concepts');
      $errors = $this->db->error();
      if($errors['code'] == '1451') return "No se puede eliminar el concepto, ya que posee información relacionada";
      if(!$result) return "Error al intentar Usuario";

    }else{
      return "El Id del concepto no existe en la base de datos";
    }
    return true;
  }

  public function validateData($code, $concept_group_id,$concept_type_id,$concept_movement_id){

    //Repeated code validation
    $query = $this->db->get_where('credit_debit_concepts', ['code' => $code]);
    if ($query->num_rows() > 0) return "Ya existe un concepto con el código ingresado";

    return $this->validateIDs($concept_group_id,$concept_type_id,$concept_movement_id);

  }

  public function validateIDs($concept_group_id,$concept_type_id,$concept_movement_id){

     //Concept group existence validation
    $query = $this->db->get_where('concept_group', ["concept_group_id" => $concept_group_id]);
    if ($query->num_rows() <= 0) return "No existe el grupo especificado";

    //Concept type existence validation
    $query = $this->db->get_where('concept_type', ["concept_type_id" => $concept_type_id]);
    if ($query->num_rows() <= 0) return "No existe el tipo de concepto especificado";

    //Concept movement existence validation
    $query = $this->db->get_where('concept_movement', ["concept_movement_id" => $concept_movement_id]);
    if ($query->num_rows() <= 0) return "No existe el tipo de movimiento especificado";

    return "OK";

  }

}
