<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller;

class ConceptController extends AuthController{

    private $token_valid;
    protected $access = "ABMconceptosdebitocredito";
    function __construct(){
        parent::__construct();
        $this->load->model('Concept');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }


    //Autocomplete service for concepts
    public function conceptData_get(){

        $description = $this->get('description') ?? "";

        $cdConceptData = $this->Concept->getByDescriptionLike($description);
        return $this->response($cdConceptData, REST_Controller::HTTP_OK);

    }

}
