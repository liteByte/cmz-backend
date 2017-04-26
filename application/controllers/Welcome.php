<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
    

    function __construct(){
        parent::__construct();
        $this->load->library('pdf');
    }
    public function index(){

        $data = [
            "name" => 'PDF-CMZ'
            
        ];

        $this->pdf->load_view('welcome_message', $data);
        $this->pdf->render();
//        $this->pdf->stream("welcome.pdf");
        $this->pdf->stream('my.pdf',array('Attachment'=>0));
    }
}
