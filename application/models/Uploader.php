<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use XBase\Table;

class Uploader extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    public function processFemebaFile($medical_insurance_id,$period,$uploadData){

        /**
         *  Obtain DBF data
         */
        $table = new Table($uploadData['full_path']);
        $columns = $table->getColumns();

        //This array will store all benefits that came from the DBF
        $benefitArray = [];

        while ($registroPrestacion = $table->nextRecord()) {

            //Parse each column
            $prestacionDBF = [];
            foreach ($columns as $column) {
                $prestacionDBF[$column->name] = $registroPrestacion->forceGetString($column->name);
            }

            //Add the benefit to the array
            $benefitArray [] = $prestacionDBF;

        }

        /**
         *  Obtain DBF data
         */
        print_r($benefitArray);die();

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }

    public function processOsdeFile($medical_insurance_id,$period,$uploadData){

        /**
         *  Obtain TXT data
         */
        //Open the file
        $archivoOsde = fopen($uploadData['full_path'], "r");

        //If the file is open, read and parse every benefit. Then, save then valorized
        if ($archivoOsde) {

            while (($registroPrestacion = fgets($archivoOsde)) !== false) {

                print_r($registroPrestacion);die();

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






