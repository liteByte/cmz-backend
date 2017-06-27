<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use XBase\Table;

class Uploader extends CI_Model{

    public function __construct(){
        $this->load->model('Plan');
        $this->load->model('Nomenclator');
        $this->load->model('Professionals');
        $this->load->model('Benefit');
        $this->load->model('Affiliate');
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
                $invalidBenefits [] = ['plan' => $benefit['os'] , 'nomenclator_code' => $benefit['practica'], 'registration_number' => $benefit['matricula'], 'errorMessage' => 'No se encontró un plan cuyo código de plan concuerde con el código del plan ingresado'];
                continue;
            }


            //Validate professional's registration number
            if(!$this->Professionals->existProfessionalRegistrationNumber($benefit['matricula'])) {
                $invalidBenefits [] = ['plan' => $benefit['os'] , 'nomenclator_code' => $benefit['practica'], 'registration_number' => $benefit['matricula'], 'errorMessage' => 'No se encontró un profesional cuya matrícula concuerde con la matrícula ingresada'];
                continue;
            }

            //Validate nomenclators
            if(!$this->Nomenclator->existNomenclator($benefit['practica'])) {
                $invalidBenefits [] = ['plan' => $benefit['os'] , 'nomenclator_code' => $benefit['practica'], 'registration_number' => $benefit['matricula'], 'errorMessage' => 'No se encontró un código de nomenclador que concuerde con el código ingresado'];
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
            $this->db->select('N.nomenclator_id, N.unity');
            $this->db->from('nomenclators N');
            $this->db->where('N.code',$benefit['practica']);
            $query = $this->db->get();

            $nomenclator_id = $query->row()->nomenclator_id;
            $unit           = $query->row()->unity;


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
                'value_unit'                       => $unit,
                'state'                            => 1,  //The benefit form FEMEBA is already valued
                'active'                           => 'active'
            );

            $this->db->insert('benefits', $data);
            if ($this->db->affected_rows() == 0) return ['status' => 'error', 'msg' => 'Error inesperado: No se pudo grabar alguna de las prestaciones del archivo. Se cancela la carga'];

        }

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }

    public function processOsdeFile($medical_insurance_id,$period,$uploadData){

        /**
         *  Obtain TXT data
         */
        //Open the file
        $osdeFile = fopen($uploadData['full_path'], "r");

        //If the file is open, read and parse every benefit
        $benefitArray = [];
        if ($osdeFile) {

            while (($benefitRegistry = fgets($osdeFile)) !== false) {

                $newOsdeBenefit = [];

                //Plan description
                $newOsdeBenefit['plan_description'] = trim(substr($benefitRegistry, 253, 5));

                //Registration number (osde)
                $newOsdeBenefit['osde_registration_number'] = substr($benefitRegistry, 1, 6);

                //Period
                $date = new DateTime();
                $date->setDate('20'.substr($benefitRegistry, 56, 2), substr($benefitRegistry, 54, 2), 01);
                $newOsdeBenefit['period'] = $date->format('Y-m-d');

                //Nomenclator code
                $newOsdeBenefit['nomenclator_code'] = trim(substr($benefitRegistry, 24, 15));

                //Quantity
                $newOsdeBenefit['quantity'] = (int)(substr($benefitRegistry, 46, 3));

                //Billing code
                $value1 = (substr($benefitRegistry, 97, 3));
                $value2 = (substr($benefitRegistry, 169, 3));

                if($value1 != '000' && $value2 == '000'){
                    $newOsdeBenefit['billing_code'] = 1;
                }elseif($value1 == '000' && $value2 != '000'){
                    $newOsdeBenefit['billing_code'] = 2;
                }else{
                    $newOsdeBenefit['billing_code'] = 3;
                }

                //Benefit date
                $benefit_date = new DateTime();
                $benefit_date->setDate('20'.substr($benefitRegistry, 56, 2), substr($benefitRegistry, 54, 2), substr($benefitRegistry, 52, 2));
                $newOsdeBenefit['benefit_date'] = $benefit_date->format('Y-m-d');

                //Affiliate ID
                $newOsdeBenefit['affiliate_number'] = trim(substr($benefitRegistry, 10, 11));
                $newOsdeBenefit['affiliate_name']   = trim(substr($benefitRegistry, 339, 30));

                $benefitArray [] = $newOsdeBenefit;

            }

            fclose($osdeFile);

        } else {

            return ['status' => 'error', 'msg' => 'Error al leer el archivo enviado', 'invalidBenefits' => []];

        }

        /**
         *  Check each benefit has valid data. If not, add it to the array of the invalid benefits
         */
        $invalidBenefits = [];

        foreach ($benefitArray as $benefit){

            //Validate Plan
            if(!$this->Plan->existPlanDescription($benefit['plan_description'])) {
                $invalidBenefits [] = ['plan' => $benefit['plan_description'], 'nomenclator_code' => $benefit['nomenclator_code'], 'registration_number' => $benefit['osde_registration_number'], 'errorMessage' => 'No se encontró un plan que concuerde con la descripción del plan ingresado'];
                continue;
            }

            //Validate professional's registration number (the one from OSDE)
            if(!$this->Professionals->existProfessionalOsdeRegistrationNumber($benefit['osde_registration_number'])) {
                $invalidBenefits [] = ['plan' => $benefit['plan_description'], 'nomenclator_code' => $benefit['nomenclator_code'], 'registration_number' => $benefit['osde_registration_number'], 'errorMessage' => 'No se encontró un profesional cuya matrícula de OSDE coincida con la ingresada'];
                continue;
            }

            //Validate nomenclators
            if(!$this->Nomenclator->existNomenclator($benefit['nomenclator_code'])) {
                $invalidBenefits [] = ['plan' => $benefit['plan_description'], 'nomenclator_code' => $benefit['nomenclator_code'], 'registration_number' => $benefit['osde_registration_number'], 'errorMessage' => 'No se encontró un código de nomenclador que coincida con el ingresado'];
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
            $this->db->like('P.description', $benefit['plan_description'],'after');
            $query = $this->db->get();

            $plan_id = $query->row()->plan_id;


            //Get professional's ID
            $this->db->select('P.id_professional_data');
            $this->db->from('professionals P');
            $this->db->where('LPAD(P.osde,6,"0")',$benefit['osde_registration_number']);
            $query = $this->db->get();

            $id_professional_data = $query->row()->id_professional_data;


            //Get nomenclator's ID
            $this->db->select('N.nomenclator_id');
            $this->db->from('nomenclators N');
            $this->db->where('N.code',$benefit['nomenclator_code']);
            $query = $this->db->get();

            $nomenclator_id = $query->row()->nomenclator_id;


            //Affiliate validation (if it exists, get the ID. If not, save it)
            if(!$this->Affiliate->checkExistence($benefit['affiliate_number'])){

                $result = $this->Affiliate->save($medical_insurance_id,$plan_id,$benefit['affiliate_number'],$benefit['affiliate_name']);
                if ($result['status']){
                    $affiliate_id = $result['affiliate_id'];
                }else{
                    return ['status' => 'error', 'msg' => 'Error inesperado: No se pudo guardar el afiliado '.$benefit['affiliate_number'].'-'.$benefit['affiliate_name']];
                }

            }else{

                $affiliate_id = $this->Affiliate->getAffiliateByNumber($benefit['affiliate_number'])['affiliate_id'];

            }


            //Save the benefit. Osde must be valorized, so we use the save method from benefits which saves and valorizes it.
            $result = $this->Benefit->save($medical_insurance_id, $plan_id, $id_professional_data, $period, '0', 1, $nomenclator_id, $benefit['quantity'], $benefit['billing_code'], 100, 1, 1, 1, '0.00', $benefit['benefit_date'], $affiliate_id, 0, '0', null, null,null, null);

            if ($result['status'] == 'error') return ['status' => 'error', 'msg' => 'Error inesperado: No se pudo grabar alguna de las prestaciones del archivo. Se cancela la carga', 'invalidBenefits' => []];


        }

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }

    public function processSwissMedicalFile($medical_insurance_id,$period,$uploadData){

        return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidBenefits' => []];

    }

    public function processARBA($uploadData){

        //Get all professional's cuits
        $this->db->select('replace(F.cuit,\'-\',\'\') as cuit, F.id_fiscal_data, 0 as updated');
        $this->db->from('professionals P');
        $this->db->join('fiscal_data F','F.id_fiscal_data = P.id_fiscal_data');
        $query = $this->db->get();

        if (!$query)                 return ['status' => 'error', 'msg' => 'Error inesperado al obtener los datos de los profesionales','invalidProfessionals'=>[]];
        if ($query->num_rows() == 0) return ['status' => 'error', 'msg' => 'No se encontró informacion de profesionales','invalidProfessionals'=>[]];

        $professionalArray = [];

        foreach($query->result() as $row)
        {
            $professionalArray[$row->cuit] = ['id_fiscal_data' => $row->id_fiscal_data, 'found' => 0]; // add each user id to the array
        }

        /**
         *  Obtain TXT data
         */
        //Open the file
        $arbaFile = fopen($uploadData['full_path'], "r");

        if ($arbaFile) {

            while (($register = fgets($arbaFile)) !== false) {

                //4-> CUIT
                //8-> Alicuota a actualizar
                $alicuotaRegister = explode(';',$register);

                if(array_key_exists($alicuotaRegister[4], $professionalArray)){
                    $professionalArray[$alicuotaRegister[4]]['found'] = 1;
                    $this->db->where("id_fiscal_data", $professionalArray[$alicuotaRegister[4]]['id_fiscal_data']);
                    $this->db->update('fiscal_data', ['iibb_percentage' => str_replace(',','.',$alicuotaRegister[8])]);
                }

            }

            fclose($arbaFile);

            //Check if all professionals were found
            $notFoundProfessionals = [];
            foreach ($professionalArray as $professional){

                if($professional['found'] == 0){
                    $notFoundProfessionals [] = $professional;
                }

            }

            if(count($notFoundProfessionals) > 0) {
                return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente. Hubo profesionales cuyo cuit no se encontro en el archivo', 'invalidProfessionals' => $notFoundProfessionals];
            }else{
                return ['status' => 'ok', 'msg' => 'Archivo procesado correctamente', 'invalidProfessionals' => $notFoundProfessionals];
            }

        }else{

            return ['status' => 'error', 'msg' => 'Error al leer el archivo enviado','invalidProfessionals'=>[]];

        }

    }




}






