<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Plan extends CI_Model{

  private $description;
  private $medical_insurance_denom;
  private $medical_insurance_id;

  public function __construct(){
    parent::__construct();
  }

  //Creates the plan in 'plans'
  public function save($description,$medical_insurance_denom,$medical_insurance_id){

    $data = array(
        'description'                 => $description,
        'medical_insurance_id'        => $medical_insurance_id,
        'medical_insurance_denom'     => $medical_insurance_denom,
        'active'                      => 'active'
    );

    $this->db->insert('plans', $data);

    return true;

  }

  //Updates the plan in 'plans'
  public function update($description,$medical_insurance_denom,$medical_insurance_id,$id,$userID){

    $now = date('Y-m-d H:i:s');

    $data = array(
        'description'                 => $description,
        'medical_insurance_id'        => $medical_insurance_id,
        'medical_insurance_denom'     => $medical_insurance_denom,
        'active'                      => 'active',
        'update_date'                 => $now,
        'modify_user_id'              => $userID
    );

    $this->db->where('plan_id', $id);
    $this->db->update('plans', $data);

    return true;

  }

  //Get all plans of a certain medical insurance
  public function getPlansByInsuranceID($medicalInsuranceID){

    $result = array();

    $this->db->where(array('active' => "active",'medical_insurance_id' => $medicalInsuranceID));
    $this->db->order_by("description", "asc");
    $this->db->order_by("medical_insurance_denom", "asc");
    $query = $this->db->get('plans');

    foreach ($query->result_array('Plan') as $row){
      array_push($result,$row);
    }

    return $result;

  }

  //Get a specific plan information
  public function getPlanById($planID){

    $result = array();

    $query = $this->db->get_where('plans', array("plan_id" => $planID));

    return $query->row();
  }

  //Delete plan information in 'plans'
  //TODO:El sistema valida que el Plan al ser eliminado no esté asociado a liquidaciones actuales o pasadas.
  public function delete($planID,$userID){

    $now = date('Y-m-d H:i:s');
    $query = $this->db->get_where('plans', ["plan_id" => $planID]);

    if($query->num_rows()){
      //Delete insurance
      $this->db->where('plan_id', $planID);
      $result = $this->db->delete('plans');
      $errors = $this->db->error();
      if($errors['code'] == '1451') return "No se puede eliminar el Plan, ya que posee información relacionada";
      if(!$result) return "Error al intentar eliminar plan";
    }else{
      return "El Id del plan no existe en la base de datos";
    }
    return true;
  }

  public function validateData($description,$medical_insurance_id){

    //Repeated description validation
    $query = $this->db->get_where('plans', array('description' => $description,'medical_insurance_id' => $medical_insurance_id));
    if ($query->num_rows() > 0) return "Ya existe un plan con la descripción indicada para la obra social informada";

    //Medical insurance existence validation
    $query = $this->db->get_where('medical_insurance', array("medical_insurance_id" => $medical_insurance_id,'active' => 'active'));
    if ($query->num_rows() <= 0) return "No existe la obra social especificada";

    return "OK";

  }

  public function validateDataOnUpdate($description,$medical_insurance_id,$id){

    //Repeated description validation
    $query = $this->db->get_where('plans', array('description' => $description,'medical_insurance_id' => $medical_insurance_id,'plan_id !='=>$id));
    if ($query->num_rows() > 0) return "Ya existe un plan con la descripción indicada para la obra social informada";

    //Medical insurance existence validation
    $query = $this->db->get_where('medical_insurance', array("medical_insurance_id" => $medical_insurance_id,'active' => 'active'));
    if ($query->num_rows() <= 0) return "No existe la obra social especificada";

    return "OK";

  }

}
