<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Professionals extends CI_Model{

    private $table = "professionals";
   
    public function __construct(){
        parent::__construct();
    }

    public function validateData($document_number, $speciality_id, $id_category_femeba, $id_medical_career, $id_payment_type, $bank_id){
        $query = $this->db->get_where('professionals', array('document_number' => $document_number));
        if($query){
            if ($query->num_rows() > 0) return "El numero de documento ya esta registrado";
        }

        $query = $this->db->get_where('specialities', array('speciality_id' => $speciality_id));
        if ($query->num_rows() == 0) return "La especilidad no registrada";

        if($id_category_femeba <> '')
            $query = $this->db->get_where('category_femeba', array('id_category_femeba' => $id_category_femeba));
            if ($query->num_rows() == 0) return "Categoria FEMEBA, no registrada";

        if($id_medical_career <> '')
            $query = $this->db->get_where('medical_career', array('id_medical_career' => $id_medical_career));
            if ($query->num_rows() == 0) return "Carrera medica no registrada";

        $query = $this->db->get_where('payment_type', array('id_payment_type' => $id_payment_type));
        if ($query->num_rows() == 0) return "Tipo de pago no registrado";

        if(!empty($bank_id))
            $query = $this->db->get_where('banks', array('bank_id' => $bank_id));
            if ($query->num_rows() == 0) return "Banco no registrado";

        return "OK";
    }

    public function save($registration_number, $name, $document_type, $document_number, $date_birth, $legal_address, $legal_locality, $zip_code, $phone_number, $email, $office_address, $office_locality, $cuit, $speciality_id, $type_partner, $id_category_femeba, $id_medical_career,  $id_payment_type, $bank_id, $date_start_activity, $iibb, $iibb_percentage, $gain, $iva_id, $retention_vat, $retention_gain, $account_number, $cbu_number ){

        $fiscal_data = array(
            "cuit"=>$cuit,
            "date_start_activity"=>$date_start_activity,
            "iibb"=>$iibb,
            "iibb_percentage"=>$iibb_percentage,
            "gain"=>$gain,
            "iva_id"=> $iva_id,
            "retention_vat"=>$retention_vat,
            "retention_gain"=>$retention_gain
        );

        $result =  $this->db->insert('fiscal_data', $fiscal_data);

        if(!$result) return "Error al intentar crear nuevo Profesionals";

        //Obtain last inserted fiscal data id
        $id_fiscal_data= $this->db->insert_id();

        if($id_fiscal_data){
            $professional_data = array(
                "registration_number"   =>$registration_number,
                "name"                  =>$name,
                "document_type"         =>$document_type,
                "document_number"       =>$document_number,
                "date_birth"            =>$date_birth,
                "legal_address"         =>$legal_address,
                "legal_locality"        =>$legal_locality,
                "zip_code"              =>$zip_code,
                "phone_number"          =>$phone_number,
                "email"                 =>$email,
                "office_address"        =>$office_address,
                "office_locality"       =>$office_locality,
                "speciality_id"         =>$speciality_id,
                "type_partner"          =>$type_partner,
                "id_category_femeba"    =>$id_category_femeba,
                "id_medical_career"     =>$id_medical_career,
                "id_payment_type"       =>$id_payment_type,
                "bank_id"               =>$bank_id,
                "account_number"        =>$account_number,
                "cbu_number"            =>$cbu_number,
                "id_fiscal_data"        =>$id_fiscal_data,
                "active"                =>"active"
            );

            $result =  $this->db->insert('professionals', $professional_data);
            $errors = $this->db->error();

  

            if(!$result){ $result = "Error al intentar crear nuevo Profesional"; return $result; }
        }
        return 1;
    }

    public function getProfessionals(){
        $result = array();

        $this->db->select('professionals.* , fiscal_data.*, specialities.description as specialty, medical_career.*' );
        $this->db->from ( 'professionals' );
        $this->db->join('fiscal_data',      'fiscal_data.id_fiscal_data = professionals.id_fiscal_data');
        $this->db->join('specialities',      'specialities.speciality_id = professionals.speciality_id');
        $this->db->join('medical_career',   'medical_career.id_medical_career = professionals.id_medical_career');
        $this->db->order_by("name", "asc");
        $this->db->where('professionals.active',"active");
        $query =  $this->db->get();

        if(!$query){ return false;  }

        foreach ($query->result_array('Professionals') as $row){
            array_push($result,$row);
        }
        return $result;
    }

    public function getProfessionalsById($id){
        $result = array();

        $this->db->select('professionals.bank_id' );
        $query = $this->db->get_where('professionals', array('professionals.active' => "active", 'id_professional_data' => $id ));
        $bank_result = $query->row()->bank_id;


        if($bank_result != 0 )
            $this->db->select('professionals.* , fiscal_data.*, banks.bank_id, banks.bank_code, banks.corporate_name, specialities.*, payment_type.*, category_femeba.*, medical_career.*' );
        else
            $this->db->select('professionals.* , fiscal_data.*, specialities.*, payment_type.*, category_femeba.*, medical_career.*' );
        $this->db->join('fiscal_data', 'professionals.id_fiscal_data = fiscal_data.id_fiscal_data');
        if($bank_result != 0 )
            $this->db->join('banks', 'banks.bank_id = professionals.bank_id');
        $this->db->join('medical_career', 'medical_career.id_medical_career = professionals.id_medical_career');
        $this->db->join('specialities', 'specialities.speciality_id = professionals.speciality_id');
        $this->db->join('payment_type', 'payment_type.id_payment_type = professionals.id_payment_type');
        $this->db->join('category_femeba', 'category_femeba.id_category_femeba = professionals.id_category_femeba');
        $this->db->order_by("name", "asc");
        $query = $this->db->get_where('professionals', array('professionals.active' => "active", 'id_professional_data' => $id ));

        foreach ($query->result_array('Professionals') as $row){
            array_push($result,$row);
        }
        return $result;
    }

    public function update($id, $registration_number, $name, $document_type, $document_number, $date_birth, $legal_address, $legal_locality, $zip_code, $phone_number, $email, $office_address, $office_locality, $id_fiscal_data, $speciality_id, $type_partner, $id_category_femeba, $id_medical_career,  $id_payment_type, $bank_id, $date_start_activity, $iibb, $iibb_percentage, $gain, $iva_id, $retention_vat, $retention_gain, $cuit, $account_number, $cbu_number){

        $data = array(
            "registration_number"   =>$registration_number,
            "name"                  =>$name,
            "document_type"         =>$document_type,
            "document_number"       =>$document_number,
            "date_birth"            =>$date_birth,
            "legal_address"         =>$legal_address,
            "legal_locality"        =>$legal_locality,
            "zip_code"              =>$zip_code,
            "phone_number"          =>$phone_number,
            "email"                 =>$email,
            "office_address"        =>$office_address,
            "office_locality"       =>$office_locality,
            "type_partner"          =>$type_partner,
            "id_category_femeba"    =>$id_category_femeba,
            "id_medical_career"     =>$id_medical_career,
            "id_payment_type"       =>$id_payment_type,
            "bank_id"               =>$bank_id,
            "account_number"        =>$account_number,
            "cbu_number"            =>$cbu_number,
        );

        $this->db->where('id_professional_data', $id);
        $result = $this->db->update('professionals', $data);

        if(!$result){
            return false;
        }
        // Update table fiscal_data
        $data = array(
                "cuit"=>$cuit,
                "date_start_activity"=>$date_start_activity,
                "iibb"=>$iibb,
                "iibb_percentage"=>$iibb_percentage,
                "gain"=>$gain,
                "iva_id"=> $iva_id,
                "retention_vat"=>$retention_vat,
                "retention_gain"=>$retention_gain
        );

        $this->db->where("id_fiscal_data", $id_fiscal_data);
        $this->db->update('fiscal_data', $data);
        $afftectedRows = $this->db->affected_rows();

        if(!$afftectedRows) return false;
        return true;
    }

    public function delete($professionalId, $downUserId){
        $now = date('Y-m-d H:i:s');
        //Delete Profesionals
        $this->db->where('id_professional_data', $professionalId);
        $this->db->update('professionals', array('active' => 'inactive', 'date_update' => $now, 'down_user_id' => $downUserId));
        $afftectedRows = $this->db->affected_rows();

        if(!$afftectedRows){
            return false;
        }
        return true;
    }

    public function validateDataUpdate($id, $document_number){

        $query = $this->db->get_where('professionals', array('document_number' => $document_number, 'id_professional_data !=' => $id ));
        if ($query->num_rows() > 0) return "El número de documento ingresado ya está en uso";

        return "OK";
    }

    public function searchData($param){

        $result = [];
        
        $this->db->select('id_professional_data, registration_number, name');
        $this->db->from($this->table);
        $this->db->or_like('registration_number', $param);
        $this->db->or_like('document_number', $param);
        $this->db->or_like('name', $param);
        $this->db->order_by("registration_number ASC, document_number ASC ");
        $this->db->limit(15);
        $query = $this->db->get();


        foreach ($query->result_array() as $row){
            array_push($result, $row);
        }
        return $result;
    }

    public function existProfessionalRegistrationNumber($registrationNumber){

        $this->db->select('P.*');
        $this->db->from('professionals P');
        $this->db->where('LPAD(P.registration_number,6,"0")',$registrationNumber);
        $query = $this->db->get();

        if (!$query)                 return false;
        if ($query->num_rows() == 0) return false;

        return true;

    }

}
