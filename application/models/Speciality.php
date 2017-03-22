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
    public function update($speciality_code, $description, $id) {

        $data = array(
            'speciality_code' => $speciality_code,
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

        //Delete bank
        $this->db->where('speciality_id', $specialityID);
        $this->db->update('specialities', array('active' => 'inactive'));

        return true;

    }

    public function validateData($speciality_code) {

        //Speciality code validation
        $query = $this->db->get_where('specialities', array('speciality_code' => $speciality_code));
        if ($query->num_rows() > 0) return "El código de especialidad ingresado esta siendo utilizado";

        return "OK";

    }

    public function validateDataOnUpdate($speciality_code, $id) {

        //Speciality code validation
        $query = $this->db->get_where('specialities', array('speciality_code' => $speciality_code, 'speciality_id !=' => $id));
        if ($query->num_rows() > 0) return "El código de especialidad ingresado esta siendo utilizado";

        return "OK";

    }

}
