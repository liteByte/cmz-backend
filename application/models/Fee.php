<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Fee extends CI_Model{

  public function __construct(){
      parent::__construct();
  }

  //Creates the fee in 'fees'
  public function save($medical_insurance_id, $plan_id, $fee_type_id, $upload_date, $period, $units){

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

      return $this->createUnits($units,$feeID);

  }

  //Create a fee's units
 public function createUnits($units,$feeID){

    //Add new units to the fee
    foreach ($units as $unit) {

      $this->db->insert('units', ['fee_id' => $feeID,'unit' => $unit->unit, 'movement' => $unit->movement, 'expenses' => $unit->expenses]);
      if ($this->db->affected_rows() == 0) return false;

      //Obtain last inserted fee id
      $unitID = $this->db->insert_id();

      //Add honoraries to the unit
      if (!($this->createHonoraries($unit->honoraries, $unitID))) return false;

    }

    return true;

}

 //Create a unit's honoraries
 public function createHonoraries($honoraries,$unitID){

    //Delete old honoraries from the unit
    $this->db->delete('honoraries', ['unit_id' => $unitID]);

    //Add new honoraries to the unit
    foreach ($honoraries as $honorary) {

      $honorariesData = [
          'unit_id'            => $unitID,
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

  //Updates the fee in 'fees' and its units
  public function update($units, $id, $userID){

      $now = date('Y-m-d H:i:s');

      $data = array(
                    'update_date'        => $now,
                    'modify_user_id'     => $userID
      );

      //Update fee
      $this->db->where('fee_id', $id);
      $this->db->update('fees', $data);

      //For each unit, update it's values and add/delete honoraries
      foreach ($units as $unit){

          //Update unit
          $this->db->where('unit_id', $unit->unit_id);
          $this->db->update('units', ['unit' =>strtoupper($unit->unit), 'movement' => $unit->movement, 'expenses' => $unit->expenses]);

          //Delete all old honoraries
          $this->db->delete('honoraries', ['unit_id' => $unit->unit_id]);

          foreach ($unit->honoraries as $honorary){

              $honorariesData = [
                    'unit_id'            => $unit->unit_id,
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

      //Assign each fee it's units
      foreach ($query->result_array() as $fee) {
          $fee['units'] = $this->getFeeunits($fee);
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
      $fee['units'] = $this->getFeeunits($fee);

      return $fee;

  }

 public function getFeeunits($fee){

      //Get all fee's units
      $this->db->select('U.*');
      $this->db->from ('units U');
      $this->db->order_by("U.unit", "asc");
      $this->db->where('U.fee_id',$fee['fee_id']);
      $unitQuery = $this->db->get();

      $units = $unitQuery->result_array();

      //Assign each unit it's honoraries
      foreach ($units as &$unit){

          $this->db->select('H.*');
          $this->db->from ('honoraries H');
          $this->db->order_by("H.item_name", "asc");
          $this->db->where('H.unit_id',$unit['unit_id']);
          $honorariesQuery = $this->db->get();

          $honoraries = $honorariesQuery->result_array();
          $unit['honoraries'] = $honoraries;

      }

      return $units;

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

  public function validateHonoraryQuantity($fee_type_id,$honoraryQuantity){

      //1-Arancel CMZ y 2-Arancel Femeba
      if ($fee_type_id == 1){

          $this->db->select('MC.*');
          $this->db->from ('medical_career MC');
          $query = $this->db->get();

          $medical_career_quantity = $query->num_rows();

          if($honoraryQuantity != $medical_career_quantity*8) return false;

      } else {

          $this->db->select('CF.*');
          $this->db->from ('category_femeba CF');
          $query = $this->db->get();

          $femeba_category_quantity = $query->num_rows();

          if($honoraryQuantity != $femeba_category_quantity*8) return false;

      }

      return true;

  }

}
