<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Uploader extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    public function processFemebaFile($medical_insurance_id,$period,$uploadData){

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }

    public function processOsdeFile($medical_insurance_id,$period,$uploadData){

        //Open the file
        $archivoOsde = fopen($uploadData['full_path'], "r");

        //If the file is open, read and parse every benefit. Then, save then valorized
        if ($archivoOsde) {

            while (($lineaPrestacion = fgets($archivoOsde)) !== false) {

                print_r($lineaPrestacion);die();

            }

            fclose($archivoOsde);

        } else {

            return ['status' => 'error', 'msg' => 'Error al leer el archivo enviado', 'invalidBenefits' => []];

        }

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }

    public function processSwissMedicalFile($medical_insurance_id,$period,$uploadData){

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }




}






