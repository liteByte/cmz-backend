<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// use namespace
use Restserver\Libraries\REST_Controller  as RC;
class SendMail{

    public  function recoverpassword($info, $newPassword){
        $CI =& get_instance();

        $data['password']= $newPassword;
        $data['name']= $info['data']->name;

        $CI ->email->from('pruebalitebyte@gmail.com', 'CMZ');
        $CI ->email->to($info['data']->email);
        $CI->email->subject('Recuperacion de contraseña');
        $CI->email->message($CI->load->view('email/recover_password', $data, true) );
        $CI->email->set_mailtype('html');


        if($CI->email->send()){
            return $CI->response(array('msg'=>$info['data']->email), RC::HTTP_OK);
        } else {
            return $CI->response(array('error'=>show_error($CI->email->print_debugger())), RC::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public static function signin($name, $document_number, $email, $password){
        $CI =& get_instance();
        $data['name'] =  $name;
        $data['document_number'] =  $document_number;
        $data['email'] =  $email;
        $data['password'] =  $password;

        $CI ->email->from('pruebalitebyte@gmail.com', 'CMZ');
        $CI ->email->to($data['email']);
        $CI->email->subject('Usuario CMZ');
        $CI->email->message($CI->load->view('email/signin', $data, true) );
        $CI->email->set_mailtype('html');


        if($CI->email->send()){
            return $CI->response(array('msg'=>'Problemas en el envio de email'), RC::HTTP_OK);
        } else {
            return $CI->response(array('error'=>show_error($CI->email->print_debugger())), RC::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}