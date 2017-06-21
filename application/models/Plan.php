<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Plan extends CI_Model{

  private $description;
  private $medical_insurance_id;

  public function __construct(){
    parent::__construct();
    $this->load->model('fee');
  }

  //Creates the plan in 'plans'
  public function save($description,$medical_insurance_id){

    $data = array(
        'description'                 => $description,
        'medical_insurance_id'        => $medical_insurance_id,
        'active'                      => 'active'
    );

    $this->db->insert('plans', $data);

    return true;

  }

  //Updates the plan in 'plans'
  public function update($description,$medical_insurance_id,$id,$userID){

    $now = date('Y-m-d H:i:s');

    $data = array(
        'description'                 => $description,
        'medical_insurance_id'        => $medical_insurance_id,
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

    $this->db->select('P.*,MI.denomination as medical_insurance_denom');
    $this->db->from('plans P');
    $this->db->join('medical_insurance MI', 'P.medical_insurance_id = MI.medical_insurance_id');
    $this->db->order_by("MI.denomination", "asc");
    $this->db->order_by("P.description", "asc");
    $this->db->order_by("MI.denomination", "asc");
    $this->db->where('P.medical_insurance_id',$medicalInsuranceID);
    $query = $this->db->get();

    foreach ($query->result_array() as $row){
      $result[] = $row;
    }

    return $result;

  }

  //Get a specific plan information
  public function getPlanById($planID){

    $query = $this->db->get_where('plans', array("plan_id" => $planID));

    return $query->row();
  }

  public function getPlansByFeeID($feeId){

      $fee = $this->fee->getFeeById($feeId);

      if(empty($fee)) return [];

      $this->db->select('P.plan_id,P.description');
      $this->db->from ('plans P');
      $this->db->join('fees F','P.plan_id = F.plan_id');
      $this->db->where('P.medical_insurance_id',$fee['medical_insurance_id']);
      $this->db->where('P.active','active');
      $this->db->where('F.active','active');
      $this->db->where('F.period_until',null);
      $query = $this->db->get();

      if (!$query)                 return [];
      if ($query->num_rows() == 0) return [];

      return $query->result_array();

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

  public function existPlanCode($planCode){

        $this->db->select('P.*');
        $this->db->from('plans P');
        $this->db->where('P.plan_code',$planCode);
        $query = $this->db->get();

        if (!$query)                 return false;
        if ($query->num_rows() == 0) return false;

        return true;

    }

}
