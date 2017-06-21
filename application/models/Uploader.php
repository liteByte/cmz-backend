<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use XBase\Table;

class Uploader extends CI_Model{

    public function __construct(){
        $this->load->model('Plan');
        $this->load->model('Nomenclator');
        $this->load->model('Professionals');
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
         *  Check each benefit has valid data. If not, add it to the array of the invalid benefits
         */
        $invalidBenefits = [];

        foreach ($benefitArray as $benefit){

            //Validate Plan
            if(!$this->Plan->existPlanCode($benefit['os'])) {
                $invalidBenefits [] = $benefit['os'] . ' - ' . $benefit['practica'] . ' - ' . $benefit['matricula'];
                continue;
            }


            //Validate professional's registration number
            if(!$this->Professionals->existProfessionalRegistrationNumber($benefit['matricula'])) {
                $invalidBenefits [] = $benefit['os'] . ' - ' . $benefit['practica'] . ' - ' . $benefit['matricula'];
                continue;
            }

            //Validate nomenclators
            if(!$this->Nomenclator->existNomenclator($benefit['practica'])) {
                $invalidBenefits [] = $benefit['os'] . ' - ' . $benefit['practica'] . ' - ' . $benefit['matricula'];
                continue;
            }

        }
        if (count($invalidBenefits) > 0) {
            $cantidadInvalida = count($invalidBenefits);
            return ['status' => 'error', 'msg' => 'Se han encontrados registros invalidos en el archivo ingresado ('.$cantidadInvalida.')' , 'invalidBenefits' => $invalidBenefits];
        }

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






