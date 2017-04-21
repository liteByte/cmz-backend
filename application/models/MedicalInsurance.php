<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class MedicalInsurance extends CI_Model{

    private $denomination;
    private $settlement_name;
    private $address;
    private $location;
    private $postal_code;
    private $website;
    private $cuit;
    private $iva_id;
    private $gross_income;
    private $payment_deadline;
    private $scope_id;
    private $femeba;
    private $ret_jub_femeba;
    private $federation_funds;
    private $admin_rights;
    private $ret_socios_honorarios;
    private $ret_socios_gastos;
    private $ret_nosocios_honorarios;
    private $ret_nosocios_gastos;
    private $ret_adherente_honorarios;
    private $ret_adherente_gastos;
    private $cobertura_fer_noct;

    //Name of table

    private $table = "medical_insurance";

    public function __construct(){
        parent::__construct();
    }

    //Creates the medical insurace in 'medical_insurance'
    public function save($denomination,$settlement_name,$address,$location,$postal_code,$website,$cuit,$iva_id,$gross_income,$payment_deadline,$scope_id,$maternal_plan,$femeba,$ret_jub_femeba,$federation_funds,$admin_rights,$ret_socios_honorarios,$ret_socios_gastos,$ret_nosocios_honorarios,$ret_nosocios_gastos,$ret_adherente_honorarios,$ret_adherente_gastos,$cobertura_fer_noct, $judical, $print){

        $data = array(
            'denomination'                => $denomination,
            'settlement_name'             => $settlement_name,
            'address'                     => $address,
            'location'                    => $location,
            'postal_code'                 => $postal_code,
            'website'                     => $website,
            'cuit'                        => $cuit,
            'iva_id'                      => $iva_id,
            'gross_income'                => $gross_income,
            'payment_deadline'            => $payment_deadline,
            'scope_id'                    => $scope_id,
            'maternal_plan'               => $maternal_plan,
            'admin_rights'                => $admin_rights,
            'femeba'                      => $femeba,
            'ret_jub_femeba'              => $ret_jub_femeba,
            'federation_funds'            => $federation_funds,
            'ret_socios_honorarios'       => $ret_socios_honorarios,
            'ret_socios_gastos'           => $ret_socios_gastos,
            'ret_nosocios_honorarios'     => $ret_nosocios_honorarios,
            'ret_nosocios_gastos'         => $ret_nosocios_gastos,
            'ret_adherente_honorarios'    => $ret_adherente_honorarios,
            'ret_adherente_gastos'        => $ret_adherente_gastos,
            'cobertura_fer_noct'          => $cobertura_fer_noct,
            'active'                      => 'active',
            'judicial'                    => $judical,
            'print'                       => $print

        );

        $this->db->insert('medical_insurance', $data);

        return true;

    }

    //Updates the medical insurance in 'medical_insurance'
    public function update($denomination,$settlement_name,$address,$location,$postal_code,$website,$cuit,$iva_id,$gross_income,$payment_deadline,$scope_id,$maternal_plan,$femeba,$ret_jub_femeba,$federation_funds,$admin_rights,$ret_socios_honorarios,$ret_socios_gastos,$ret_nosocios_honorarios,$ret_nosocios_gastos,$ret_adherente_honorarios,$ret_adherente_gastos,$cobertura_fer_noct,$id,$userID, $judical, $print){

        $now = date('Y-m-d H:i:s');

        $data = array(
            'denomination'                => $denomination,
            'settlement_name'             => $settlement_name,
            'address'                     => $address,
            'location'                    => $location,
            'postal_code'                 => $postal_code,
            'website'                     => $website,
            'cuit'                        => $cuit,
            'iva_id'                      => $iva_id,
            'gross_income'                => $gross_income,
            'payment_deadline'            => $payment_deadline,
            'scope_id'                    => $scope_id,
            'maternal_plan'               => $maternal_plan,
            'admin_rights'                => $admin_rights,
            'femeba'                      => $femeba,
            'ret_jub_femeba'              => $ret_jub_femeba,
            'federation_funds'            => $federation_funds,
            'ret_socios_honorarios'       => $ret_socios_honorarios,
            'ret_socios_gastos'           => $ret_socios_gastos,
            'ret_nosocios_honorarios'     => $ret_nosocios_honorarios,
            'ret_nosocios_gastos'         => $ret_nosocios_gastos,
            'ret_adherente_honorarios'    => $ret_adherente_honorarios,
            'ret_adherente_gastos'        => $ret_adherente_gastos,
            'cobertura_fer_noct'          => $cobertura_fer_noct,
            'active'                      => 'active',
            'update_date'                 => $now,
            'modify_user_id'              => $userID,
            'judical'                     => $judical,
            'print'                       => $print
        );

        $this->db->where('medical_insurance_id', $id);
        $this->db->update('medical_insurance', $data);

        return true;

    }

    //Get all medical insurance information
    public function getMedicalInsurances(){

        $result = array();

        $this->db->select('medical_insurance_id,denomination,femeba,location,address,cuit');
        $this->db->where(array('active' => "active"));
        $this->db->order_by("denomination", "asc");
        $query = $this->db->get('medical_insurance');

        foreach ($query->result_array('MedicalInsurance') as $row){
            array_push($result,$row);
        }

        return $result;

    }

    //Get a specific medical insurance information
    public function getInsuranceById($insuranceID){

        $result = array();

        $query = $this->db->get_where('medical_insurance', array("medical_insurance_id" => $insuranceID));

        return $query->row();
    }

    //Delete medical insurance information in 'medical_insurance'
    //TODO:El sistema valida que la Obra Social al ser eliminada no esté relacionada a liquidaciónes actuales o históricas de prestaciones a profesionales.
    public function delete($insuranceID,$userID){

        $query = $this->db->get_where($this->table, ["medical_insurance_id" => $insuranceID, "judicial" => 0]);

        if($query->num_rows()){
            //Delete insurance
            $now = date('Y-m-d H:i:s');
            $this->db->where("medical_insurance_id", $insuranceID);
            $this->db->update($this->table, array('active' => 'inactive','modify_user_id' => $userID, 'update_date' => $now));
        }else{
            return false;
        }
        return true;
    }

    public function validateData($cuit, $iva_id, $scope_id){

        //CUIT validation
        $query = $this->db->get_where('medical_insurance', array('cuit' => $cuit));
        if ($query->num_rows() > 0) return "El CUIT ingresado está siendo utilizado";

        //Iva existance validation
        $query = $this->db->get_where('iva', array('iva_id' => $iva_id));
        if ($query->num_rows() == 0) return "El tipo de IVA ingresado no existe";

        //Scope existance validation
        $query = $this->db->get_where('scopes', array('scope_id' => $scope_id));
        if ($query->num_rows() == 0) return "El tipo de alcance ingresado no existe";

        return "OK";

    }

    public function validateDataOnUpdate($cuit, $iva_id, $scope_id, $id){

        //CUIT validation
        $query = $this->db->get_where('medical_insurance', array('cuit' => $cuit,'medical_insurance_id !='=>$id));
        if ($query->num_rows() > 0) return "El CUIT ingresado está siendo utilizado";

        //Iva existance validation
        $query = $this->db->get_where('iva', array('iva_id' => $iva_id));
        if ($query->num_rows() == 0) return "El tipo de IVA ingresado no existe";

        //Scope existance validation
        $query = $this->db->get_where('scopes', array('scope_id' => $scope_id));
        if ($query->num_rows() == 0) return "El tipo de alcance ingresado no existe";

        return "OK";

    }

}
