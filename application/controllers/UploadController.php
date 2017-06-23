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
        $this->load->library('excel');
        $this->load->model('Uploader');
        $this->load->library('Pdf');
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






}
