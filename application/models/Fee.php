<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Fee extends CI_Model{

  public function __construct(){
      parent::__construct();
  }

  //Creates the fee in 'fees'
  public function save($medical_insurance_id, $plan_id, $fee_type_id, $upload_date, $period, $unities){

      //Make the fee structure
      $feeData = array(
                    'medical_insurance_id'  => $medical_insurance_id,
                    'plan_id'               => $plan_id,
                    'fee_type_id'           => $fee_type_id,
                    'upload_date'           => $upload_date,
                    'period'                => $period,
                    'active'                => "active"
      );

      $this->db->insert('fees', $feeData);

      //Obtain last inserted fee id
      $feeID = $this->db->insert_id();

      return $this->createUnities($unities,$feeID);

  }

  //Create a fee's unities
 public function createUnities($unities,$feeID){

    //Add new unities to the fee
    foreach ($unities as $unity) {

      $this->db->insert('unities', ['fee_id' => $feeID,'unity' => $unity->unity, 'movement' => $unity->movement, 'expenses' => $unity->expenses]);
      if ($this->db->affected_rows() == 0) return false;

      //Obtain last inserted fee id
      $unityID = $this->db->insert_id();

      //Add honoraries to the unity
      if (!($this->createHonoraries($unity->honoraries, $unityID))) return false;

    }

    return true;

}

 //Create a unity's honoraries
 public function createHonoraries($honoraries,$unityID){

    //Delete old honoraries from the unity
    $this->db->delete('honoraries', ['unity_id' => $unityID]);

    //Add new honoraries to the unity
    foreach ($honoraries as $honorary) {

      $honorariesData = [
          'unity_id'            => $unityID,
          'movement'            => $honorary->movement,
          'value'               => $honorary->value,
          'id_medical_career'   => (empty($honorary->id_medical_career) ? null : $honorary->id_medical_career),
          'id_category_femeba'  => (empty($honorary->id_category_femeba) ? null : $honorary->id_category_femeba),
          'item_name'           => $honorary->item_name
      ];

      $this->db->insert('honoraries', $honorariesData);
      if ($this->db->affected_rows() == 0) return false;
    }

    return true;
}

  //Updates the fee in 'fees' and its unities
  public function update($unities, $id, $userID){

      $now = date('Y-m-d H:i:s');

      $data = array(
                    'update_date'        => $now,
                    'modify_user_id'     => $userID
      );

      //Update fee
      $this->db->where('fee_id', $id);
      $this->db->update('fees', $data);

      //For each unity, update it's values and add/delete honoraries
      foreach ($unities as $unity){

          //Update unity
          $this->db->where('unity_id', $unity->unity_id);
          $this->db->update('unities', ['unity' =>strtoupper($unity->unity), 'movement' => $unity->movement, 'expenses' => $unity->expenses]);

          //Delete all old honoraries
          $this->db->delete('honoraries', ['unity_id' => $unity->unity_id]);

          foreach ($unity->honoraries as $honorary){

              $honorariesData = [
                    'unity_id'            => $unity->unity_id,
                    'movement'            => $honorary->movement,
                    'value'               => $honorary->value,
                    'id_medical_career'   => (empty($honorary->id_medical_career)  ? null : $honorary->id_medical_career),
                    'id_category_femeba'  => (empty($honorary->id_category_femeba) ? null : $honorary->id_category_femeba),
                    'item_name'           => $honorary->item_name
              ];

              $this->db->insert('honoraries', $honorariesData);

          }
      }

      return true;

  }

  //Get all fees
  public function getFees(){

      $result = array();

      $this->db->select('F.* , MI.denomination as insurance_description, P.description as plan_description, FT.description as fee_type_description');
      $this->db->from ('fees F');
      $this->db->join('medical_insurance MI',       'MI.medical_insurance_id = F.medical_insurance_id');
      $this->db->join('plans P',                    'P.plan_id = F.plan_id');
      $this->db->join('fee_types FT',               'FT.fee_type_id = F.fee_type_id');
      $this->db->order_by("MI.denomination", "asc");
      $this->db->order_by("P.description", "asc");
      $this->db->order_by("F.period", "desc");
      $this->db->where('F.active',"active");
      $query = $this->db->get();

      if (!$query)                 return [];
      if ($query->num_rows() == 0) return [];

      //Assign each fee it's unities
      foreach ($query->result_array() as $fee) {
          $fee['unities'] = $this->getFeeUnities($fee);
          $result[] = $fee;
      }

      return $result;

  }

  //Get a specific fee information
  public function getFeeById($feeID){

      $this->db->select('F.*');
      $this->db->from ('fees F');
      $this->db->where(['F.active' => "active","F.fee_id" => $feeID]);
      $query = $this->db->get();

      if (!$query)                 return [];
      if ($query->num_rows() == 0) return [];

      //Get the first fee of the associative result_array (there should be only one anyways)
      $fee = reset($query->result_array());
      $fee['unities'] = $this->getFeeUnities($fee);

      return $fee;

  }

 public function getFeeUnities($fee){

      //Get all fee's unities
      $this->db->select('U.*');
      $this->db->from ('unities U');
      $this->db->order_by("U.unity", "asc");
      $this->db->where('U.fee_id',$fee['fee_id']);
      $unityQuery = $this->db->get();

      $unities = $unityQuery->result_array();

      //Assign each unity it's honoraries
      foreach ($unities as &$unity){

          $this->db->select('H.*');
          $this->db->from ('honoraries H');
          $this->db->order_by("H.item_name", "asc");
          $this->db->where('H.unity_id',$unity['unity_id']);
          $honorariesQuery = $this->db->get();

          $honoraries = $honorariesQuery->result_array();
          $unity['honoraries'] = $honoraries;

      }

      return $unities;

 }

  //Delete fee information in 'fees'
  //TODO:El sistema valida que el Arancel al ser eliminado no esté asociado a valoración de prestaciones sean actuales o pasadas
  public function delete($feeID,$userID){

      $now = date('Y-m-d H:i:s');

      //Delete fee
      $this->db->where('fee_id', $feeID);
      $this->db->update('fees', array('active' => 'inactive','modify_user_id' => $userID,'update_date' =>$now));

      return true;

  }

  public function validateData($medical_insurance_id, $plan_id, $fee_type_id, $period){

      //Validate repetead key
      $query = $this->db->get_where('fees', ['medical_insurance_id' => $medical_insurance_id, 'plan_id' => $plan_id, 'fee_type_id' => $fee_type_id, 'period' => $period]);
      if ($query->num_rows() > 0) return "Ya existe un arancel con la misma combinación de Obra Social + Plan + Tipo Arancel + Período";

     return $this->validateIDs($medical_insurance_id,$plan_id,$fee_type_id);

   }

  public function validateIDs($medical_insurance_id,$plan_id,$fee_type_id){

    //Medical insurance existence validation
   $query = $this->db->get_where('medical_insurance', ["medical_insurance_id" => $medical_insurance_id]);
   if ($query->num_rows() <= 0) return "No existe la obra social especificada";

   //Plan existence validation
   $query = $this->db->get_where('plans', ["plan_id" => $plan_id]);
   if ($query->num_rows() <= 0) return "No existe el plan especificado";

   //Fee type existence validation
   $query = $this->db->get_where('fee_types', ["fee_type_id" => $fee_type_id]);
   if ($query->num_rows() <= 0) return "No existe el tipo de arancel especificado";

   return "OK";

 }

}
