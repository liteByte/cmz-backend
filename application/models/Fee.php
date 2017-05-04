<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Fee extends CI_Model{

  public function __construct(){
      parent::__construct();
  }

  //Creates the fee in 'fees'
  public function save($medical_insurance_id, $planArray, $fee_type_id, $upload_date, $period_since, $units){

      foreach($planArray as $plan) {

          //Get current fee and validate if it's date is newer than this one. If so, close the old one
          $currentFee = $this->getCurrentFeeByKey($medical_insurance_id, $plan, $fee_type_id)[0];

          if (!empty($currentFee)) {

              if (date($period_since) > date($currentFee['period_since'])) {

                  $close_period_date = date('Y-m-d', (strtotime('-1 month', strtotime($period_since))));
                  $data = ['period_until' => $close_period_date];

                  //Update old fee by closing it's period_until
                  $this->db->where('fee_id', $currentFee['fee_id']);
                  $this->db->update('fees', $data);

              } else {

                  return ['status' => "error", 'msg' => "Ya existe un arancel para la Obra Social, Plan y Tipo de Arancel en el período ingresado"];

              }
          }

          //Make the fee structure
          $feeData = array(
              'medical_insurance_id'    => $medical_insurance_id,
              'plan_id'                 => $plan,
              'fee_type_id'             => $fee_type_id,
              'upload_date'             => $upload_date,
              'period_since'            => $period_since,
              'period_until'            => null,
              'active'                  => "active"
          );

          $this->db->insert('fees', $feeData);

          //Obtain last inserted fee id
          $feeID = $this->db->insert_id();

          //If the DB can't create a fee's unit return error
          if(!$this->createUnits($units, $feeID)) return ['status' => "error", 'msg' => "No se pudieron crear los valores de unidades para el arancel"];

      }

      return ['status' => "ok", 'msg' => "Arancel creado satisfactoriamente"];

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
          'unit_id'             => $unitID,
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
      $this->db->order_by("F.period_since", "desc");
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
      $fee = $query->first_row('array');
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

      //Get the fee to delete
      $query = $this->db->get_where('fees', ["fee_id" => $feeID, 'active' => 'active']);
      $feeToDelete = $query->row();

      if ($query->num_rows()) {

          //Validate if the fee is the in-forge one. If so, open the last fee if there is one
          if ($feeToDelete->period_until == null) {

              //Get the previous fee (the one that was in-forge before the one being deleted)
              $this->db->select('F.*');
              $this->db->from('fees F');
              $this->db->where('F.period_until <>', null);
              $this->db->order_by("F.period_until", "desc");
              $this->db->limit(1);
              $query = $this->db->get();
              $previousFee = $query->row();

              if (!empty($previousFee)) {

                  $this->db->where('fee_id', $previousFee->fee_id);
                  $this->db->update('fees', ['period_until' => null]);

                  if (!$query)                  return false;
                  if ($query->num_rows() == 0)  return false;

              }
          }

      } else {

          return false;

      }

      $now = date('Y-m-d H:i:s');

      //Delete fee
      $this->db->where('fee_id', $feeID);
      $this->db->update('fees', array('active' => 'inactive','modify_user_id' => $userID,'update_date' =>$now));

      return true;

  }

  public function increaseFeePercentage($medical_insurance_id,$plan,$new_period_since,$increase_value){

      //Obtain the in-force fee for this plan (field period_until is null)
      $query = $this->db->get_where('fees', ['medical_insurance_id' => $medical_insurance_id, 'plan_id' => $plan, 'period_until' => null,'active' => 'active']);

      if (!$query)                 return false;
      if ($query->num_rows() == 0) return false;

      $feeToClose = $query->row();

      //Close the old fee a month before the new period.
      // If the close period is < than the since period, dont't substract a month to the close period and add a month to the new period
      $close_period_date = date('Y-m-d',(strtotime('-1 month', strtotime($new_period_since))));
      if($close_period_date < $new_period_since) $close_period_date=$new_period_since;
      $new_period_since  = date('Y-m-d',(strtotime('+1 month', strtotime($new_period_since))));

      $feeToClose->period_until = $close_period_date;
      $this->db->where('fee_id', $feeToClose->fee_id);
      $this->db->update('fees', $feeToClose);

      if ($this->db->affected_rows() == 0) return ['status' => 'error','message' => 'No se pudo cerrar periodo del arancel'];

      //Once the old fee is closed, create the new fee (same data as old fee but increased value and new period_since)
      $oldFee = $this->getFeeById($feeToClose->fee_id);
      $newUnits = array_map(array($this,"unitArrayToObject"), $oldFee['units']);


      foreach($newUnits as $unit){

          $unit->expenses = $unit->expenses + ($unit->expenses * ($increase_value / 100));

          foreach ($unit->honoraries as $honorary){
              $honorary->value = $honorary->value + ($honorary->value * ($increase_value / 100));
          }

      }

      $result = $this->save($feeToClose->medical_insurance_id, [$feeToClose->plan_id], $feeToClose->fee_type_id, $feeToClose->upload_date, $new_period_since,$newUnits);
      if($result['status'] == "error") return ['status' => 'error','message' => 'No se pudo incrementar los valores de los aranceles'];

      return true;

  }

  public function validateData($medical_insurance_id, $planArray, $fee_type_id, $period_since){

      foreach($planArray as $plan) {

          //Validate repetead key
          $query = $this->db->get_where('fees', ['medical_insurance_id' => $medical_insurance_id, 'plan_id' => $plan, 'fee_type_id' => $fee_type_id, 'period_since' => $period_since]);
          if ($query->num_rows() > 0) return "Ya existe un arancel con la misma combinación de Obra Social + Plan + Tipo Arancel + Período";

          //Validate if the insurance and plan are OK
          $query = $this->db->get_where('plans', ['medical_insurance_id' => $medical_insurance_id, 'plan_id' => $plan]);
          if ($query->num_rows() <> 1) return "El plan seleccionado no pertenece a la obra social seleccionada o viceversa";

      }

      return $this->validateIDs($medical_insurance_id,$planArray,$fee_type_id);

   }

  public function validateIDs($medical_insurance_id,$planArray,$fee_type_id){

   //Medical insurance existence validation
   $query = $this->db->get_where('medical_insurance', ["medical_insurance_id" => $medical_insurance_id]);
   if ($query->num_rows() <= 0) return "No existe la obra social especificada";

   //Plan existence validation
   $this->db->where_in('plan_id', $planArray);
   $query = $this->db->get('plans');
   if ($query->num_rows() < count($planArray) ) return "No existe alguno de los planes seleccionados";

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

  public function validateNewPeriodForPlans($medical_insurance_id,$plans,$new_period_since){

      $this->db->select('F.*');
      $this->db->from ('fees F');
      $this->db->where('medical_insurance_id', $medical_insurance_id);
      $this->db->where('active', 'active');
      $this->db->where('period_until', null);
      $this->db->where('period_since >', $new_period_since);
      $this->db->where_in('plan_id', $plans);
      $query = $this->db->get();

      if ($query->num_rows() > 0) return false;

      return true;

  }

  public function unitArrayToObject($unitArray){

      $unitObject             = new stdClass();
      $unitObject->unit       = $unitArray['unit'];
      $unitObject->movement   = $unitArray['movement'];
      $unitObject->expenses   = $unitArray['expenses'];
      $unitObject->honoraries = [];
      foreach($unitArray['honoraries'] as $honorary){
          $honoraryObject                     = new stdClass();
          $honoraryObject->movement           = $honorary['movement'];
          $honoraryObject->value              = $honorary['value'];
          $honoraryObject->id_medical_career  = $honorary['id_medical_career'];
          $honoraryObject->id_category_femeba = $honorary['id_category_femeba'];
          $honoraryObject->item_name          = $honorary['item_name'];
          $unitObject->honoraries []          = $honoraryObject;
      }

      return $unitObject;
  }

    function getCurrentFeeByKey($medical_insurance_id, $plan_id, $fee_type_id){

        $this->db->select('F.*');
        $this->db->from ('fees F');
        $this->db->where('F.medical_insurance_id',$medical_insurance_id);
        $this->db->where('F.fee_type_id',$fee_type_id);
        $this->db->where('F.plan_id',$plan_id);
        $this->db->where('F.period_until',null);
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        return $query->result_array();

    }

}
