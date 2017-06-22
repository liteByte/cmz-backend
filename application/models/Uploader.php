<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use XBase\Table;

class Uploader extends CI_Model{

    public function __construct(){
        $this->load->model('Plan');
        $this->load->model('Nomenclator');
        $this->load->model('Professionals');
        $this->load->model('Benefit');
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


        /**
         *  If EVERY benefit from the file is valid, start saving them
         */
        foreach ($benefitArray as $benefit){

            //Get plan's ID
            $this->db->select('P.plan_id');
            $this->db->from('plans P');
            $this->db->where('P.plan_code',$benefit['os']);
            $query = $this->db->get();

            $plan_id = $query->row()->plan_id;

            //Get professional's ID
            $this->db->select('P.id_professional_data');
            $this->db->from('professionals P');
            $this->db->where('LPAD(P.registration_number,6,"0")',$benefit['matricula']);
            $query = $this->db->get();

            $id_professional_data = $query->row()->id_professional_data;


            //Get nomenclator's ID
            $this->db->select('N.nomenclator_id');
            $this->db->from('nomenclators N');
            $this->db->where('N.code',$benefit['practica']);
            $query = $this->db->get();

            $nomenclator_id = $query->row()->nomenclator_id;


            //Determinate billing code
            if($benefit['honorarios'] != 0 && $benefit['gastos'] == 0){
                $billing_code_id = 1;
            }elseif ($benefit['honorarios'] == 0 && $benefit['gastos'] != 0){
                $billing_code_id = 2;
            }else{
                $billing_code_id = 3;
            }


            //Format date
            $unformatedDate = $benefit['fpca'];
            $benefit_date_unformated = str_replace('/', '-', $unformatedDate);
            $benefit_date = date('Y-m-d', strtotime($benefit_date_unformated));


            //Save the benefit. FEMEBA already has it's benefits valorized, that's why it's only saved.
            $data = array(
                'medical_insurance_id'             => $medical_insurance_id,
                'plan_id'                          => $plan_id,
                'id_professional_data'             => $id_professional_data,
                'period'                           => $period,
                'remesa'                           => 0,
                'additional'                       => 1,                                                        //Default value
                'nomenclator_id'                   => $nomenclator_id,
                'quantity'                         => $benefit['cantpca'],
                'billing_code_id'                  => $billing_code_id,
                'multiple_operation_value'         => $benefit['porcpago'],
                'holiday_option_id'                => 1,
                'maternal_plan_option_id'          => 1,
                'internment_ambulatory_option_id'  => 1,
                'unit_price'                       => 0,
                'benefit_date'                     => $benefit_date,
                'affiliate_id'                     => null,
                'bill_number'                      => null,
                'modify_coverage'                  => 0,
                'new_honorary'                     => null,
                'new_expenses'                     => null,
                'value_honorary'                   => $benefit['honorarios'],
                'value_expenses'                   => $benefit['gastos'],
                'value_unit'                       => (empty($bill_number)                                  ? null : $nomenclator->unity),
                'state'                            => 1,  //The benefit form FEMEBA is already valued
                'active'                           => 'active'
            );

            $this->db->insert('benefits', $data);
            if ($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'Error inesperado: No se pudo grabar alguna de las prestaciones del archivo'];

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






