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
        $this->load->helper(array('form', 'url'));
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    public function upload_post()    {

        $medical_insurance_id = $this->input->post('medical_insurance_id');
        $period               = $this->input->post('period');

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
        print_r($uploadData);die();
        // Special cases)
        // -Femeba          (ID 1)      -> DBF
        // -OSDE            (ID 19)     -> TXT
        // -SWISS MEDICAL   (ID 52)     ->
        if($medical_insurance_id == 1){

            $result = $this->Uploader->processFemebaFile($medical_insurance_id,$period,$uploadData);

            if($result['status'] == 'error'){
                return $this->response(['error' => $result['msg'], 'invalidBenefits' => $result['invalidBenefits']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                return $this->response(['msg' => $result['msg']], REST_Controller::HTTP_OK);
            }

        }elseif ($medical_insurance_id == 19){

            $result = $this->Uploader->processOsdeFile($medical_insurance_id,$period,$uploadData);

            if($result['status'] == 'error'){
                return $this->response(['error' => $result['msg'], 'invalidBenefits' => $result['invalidBenefits']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                return $this->response(['msg' => $result['msg']], REST_Controller::HTTP_OK);
            }

        }elseif ($medical_insurance_id == 52){

            $result = $this->Uploader->processSwissMedicalFile($medical_insurance_id,$period,$uploadData);

            if($result['status'] == 'error'){
                return $this->response(['error' => $result['msg'], 'invalidBenefits' => $result['invalidBenefits']], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                return $this->response(['msg' => $result['msg']], REST_Controller::HTTP_OK);
            }

        }else{

            //Importador generico
            print_r("Generico");die();

        }




    }






}
