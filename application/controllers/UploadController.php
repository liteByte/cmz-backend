<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class UploadController extends AuthController {

    private $token_valid;
    protected $access = "*";
    function __construct() {
        parent::__construct();
        $this->load->model('Uploader');
        $this->load->library('validator');
        $this->load->helper(array('form', 'url'));
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function upload_post()    {

        $medical_insurance_id = $this->input->post('medical_insurance_id');
        $period               = $this->input->post('period');

        if(empty($medical_insurance_id)) return $this->response(['error'=>'No se ha ingresado obra social'], REST_Controller::HTTP_BAD_REQUEST);
        if(empty($period))               return $this->response(['error'=>'No se ha ingresado período'], REST_Controller::HTTP_BAD_REQUEST);

        //Validations
        if(!$this->validator->validateDate($period)) return $this->response(['error'=>'Fecha del período inválida'], REST_Controller::HTTP_BAD_REQUEST);

        $config['upload_path']          = 'upload_benefits/';
        $config['allowed_types']        = 'csv|txt|dbf|xls|xlsx';
        $config['max_size']             = 0;
        $config['max_width']            = 0;
        $config['max_height']           = 0;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('benefit'))
        {
            return $this->response(['error' => $this->upload->display_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $uploadData = $this->upload->data();
        }

        // Special cases)
        // -Femeba          (ID 1)      -> DBF
        // -OSDE            (ID 19)     -> TXT
        // -SWISS MEDICAL   (ID 52)     ->
        if($medical_insurance_id == 1){

            if(strtoupper($uploadData['file_ext']) != '.DBF') return $this->response(['error' => 'Se esperaba un archivo .DBF para la obra social FEMEBA', 'invalidBenefits' => []], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

            $result = $this->Uploader->processFemebaFile($medical_insurance_id,$period,$uploadData);

            if($result['status'] == 'error'){
                return $this->response(['error' => $result['msg'], 'invalidBenefits' => $result['invalidBenefits']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                return $this->response(['msg' => $result['msg']], REST_Controller::HTTP_OK);
            }

        }elseif ($medical_insurance_id == 19){

            if(strtoupper($uploadData['file_ext']) != '.TXT') return $this->response(['error' => 'Se esperaba un archivo .TXT para la obra social OSDE', 'invalidBenefits' => []], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

            $result = $this->Uploader->processOsdeFile($medical_insurance_id,$period,$uploadData);

            if($result['status'] == 'error'){
                return $this->response(['error' => $result['msg'], 'invalidBenefits' => $result['invalidBenefits']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                return $this->response(['msg' => $result['msg']], REST_Controller::HTTP_OK);
            }

        }else{

            //Importador generico, no realizado aun
            return $this->response(['error' => 'No se pueden importar archivos para esta obra social actualmente', 'invalidBenefits' => []], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

        }




    }

    public function uploadARBA_post()
    {
        $config['upload_path']      = 'upload_arba/';
        $config['allowed_types']    = 'zip';
        $config['max_size']         = 0;
        $config['max_width']        = 0;
        $config['max_height']       = 0;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('arba')) {

            return $this->response(['error' => $this->upload->display_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

        } else {

            //Get the file data
            $uploadData = $this->upload->data();


            //Validate the arba file is the one for this month (fileName = PadronRGSRetMMYYYY)
            $fileMonth = substr($uploadData['raw_name'],-6,2);
            $fileYear  = substr($uploadData['raw_name'],-4,4);

            if($fileMonth != date('m') || $fileYear != date('Y')){
                unlink($uploadData['full_path']);
                unlink($uploadData['file_path'].'PadronRGSRet'.$fileMonth.$fileYear.'.txt');
                unlink($uploadData['file_path'].'PadronRGSPer'.$fileMonth.$fileYear.'.txt');
                return $this->response(['error' => 'El período del archivo no coincide con el período actual'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }


            //Unzip the file
            $zip = new ZipArchive;
            if($zip->open($uploadData['full_path']) == TRUE){
                $zip->extractTo($uploadData['file_path']);
                $zip->close();
            } else {
                unlink($uploadData['full_path']);
                return $this->response(['error' => 'Error inesperado al descromprimir el archivo'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }

        }

        //The name of the file we need is PadronRGSRetMMYYYY
        $result = $this->Uploader->processARBA($uploadData,'PadronRGSRet'.$fileMonth.$fileYear.'.txt');

        if ($result['status'] == 'error') {
            unlink($uploadData['full_path']);
            unlink($uploadData['file_path'].'PadronRGSRet'.$fileMonth.$fileYear.'.txt');
            unlink($uploadData['file_path'].'PadronRGSPer'.$fileMonth.$fileYear.'.txt');
            return $this->response(['error' => $result['msg'],'invalidProfessionals' => $result['invalidProfessionals']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            unlink($uploadData['full_path']);
            unlink($uploadData['file_path'].'PadronRGSRet'.$fileMonth.$fileYear.'.txt');
            unlink($uploadData['file_path'].'PadronRGSPer'.$fileMonth.$fileYear.'.txt');
            return $this->response(['msg' => $result['msg'],'invalidProfessionals' => $result['invalidProfessionals']], REST_Controller::HTTP_OK);
        }

    }

}
