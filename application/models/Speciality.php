<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Speciality extends CI_Model {

    private $speciality_code;
    private $description;

    public function __construct() {
        parent::__construct();
    }

    //Creates the speciality in 'specialities'
    public function save($speciality_code, $description) {

        $data = array(
            'speciality_code' => $speciality_code,
            'description' => $description,
            'active' => 'active'
        );

        $this->db->insert('specialities', $data);

        return true;

    }

    //Updates the speciality in 'specialities'
    public function update($description, $id) {

        $data = array(
            'description' => $description
        );

        $this->db->where('speciality_id', $id);
        $this->db->update('specialities', $data);

        return true;

    }

    //Get all specialities information
    public function getspecialities() {

        $result = array();

        $this->db->select('speciality_id,speciality_code,description');
        $this->db->where(array('active' => "active"));
        $this->db->order_by("description", "asc");
        $query = $this->db->get('specialities');

        foreach ($query->result_array('Speciality') as $row) {
            array_push($result, $row);
        }

        return $result;

    }

    //Get a specific speciality information
    public function getSpecialityById($specialityID) {

        $result = array();

        $query = $this->db->get_where('specialities', array("speciality_id" => $specialityID));

        return $query->row();
    }

    //Delete speciality information in 'specialities'
    //TODO:Validar que la Especialidad Médica al ser eliminada no tenga prestaciones valoradas actual e histórica con esta especialidad.
    public function delete($specialityID) {

        $query = $this->db->get_where('specialitys', array("speciality_id" => $specialityID));


        if($query->num_rows()){
            //Delete bank
            $this->db->where('speciality_id', $specialityID);
            $result = $this->db->delete('specialities');
            $errors = $this->db->error();
            if($errors['code'] == '1451') return "No se puede eliminar la especialidad, ya que posee información relacionada";
            if(!$result) return "Error al intentar especialidad";
        }else{
            return "El Id de la especialidad no existe en la base de datos";
        }
        return true;
    }

    public function validateData($speciality_code) {

        //Speciality code validation
        $query = $this->db->get_where('specialities', array('speciality_code' => $speciality_code));
        if ($query->num_rows() > 0) return "El código de especialidad ingresado esta siendo utilizado";

        return "OK";

    }

}
