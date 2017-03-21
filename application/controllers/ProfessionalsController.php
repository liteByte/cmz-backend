<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class ProfessionalsController extends AuthController{

    private $token_valid;

    function __construct(){
        parent::__construct();
        $this->load->model('Professionals');
        $this->load->library('validator');
        $this->token_valid = $this->validateToken(apache_request_headers());
    }

    //Create Professionals
    public function professionals_post(){

        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMprofesionales",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        $post = json_decode(file_get_contents('php://input'));

        $registration_number        = $post->registration_number        ?? "";
        $name                       = $post->name                       ?? "";
        $last_name                  = $post->last_name                  ?? "";
        $document_type              = $post->document_type              ?? "";
        $document_number            = $post->document_number            ?? "";
        $date_birth                 = $post->date_birth                 ?? "";
        $legal_address              = $post->legal_address              ?? "";
        $legal_locality             = $post->legal_locality             ?? "";
        $zip_code                   = $post->zip_code                   ?? "";
        $phone_number               = $post->phone_number               ?? "";
        $email                      = $post->email                      ?? "";
        $office_address             = $post->office_address             ?? "";
        $office_locality            = $post->office_locality            ?? "";
        $cuit                       = $post->cuit                       ?? "";
        $speciality_id              = $post->speciality_id              ?? "";
        $type_partner               = $post->type_partner               ?? "";
        $id_category_femeba         = $post->id_category_femeba         ?? "";
        $id_medical_career          = $post->id_medical_career          ?? "";
        $id_payment_type            = $post->id_payment_type            ?? "";
        $bank_id                    = $post->bank_id                    ?? "";
        $date_start_activity        = $post->date_start_activity        ?? "";
        $iibb                       = $post->iibb                       ?? "";
        $iibb_percentage            = $post->iibb_percentage            ?? "";
        $iva_id                     = $post->iva_id                     ?? "";
        $retention_vat              = $post->retention_vat              ?? "";
        $retention_gain             = $post->retention_gain             ?? "";
        $account_number             = $post->account_number             ?? "";
        $cbu_number                 = $post->cbu_number                 ?? "";

        if(!isset($post->gain))                   return $this->response(array('error'=>'Se debe indicar si es necesario retenerle o no ganancia al Profesional'), RC::HTTP_BAD_REQUEST);
        $gain                       = $post->gain                       ?? "";
        if(empty($registration_number))            return $this->response(array('error'=>'No se ha ingresado numero de matricula'), RC::HTTP_BAD_REQUEST);
        if(empty($name))                           return $this->response(array('error'=>'No se ha ingresado el nombre'), RC::HTTP_BAD_REQUEST);
        if(empty($last_name))                      return $this->response(array('error'=>'No se ha ingresado el apellido'), RC::HTTP_BAD_REQUEST);
        if(empty($document_type))                  return $this->response(array('error'=>'No se ha ingresado el tipo de documento'), RC::HTTP_BAD_REQUEST);
        if(empty($document_number))                return $this->response(array('error'=>'No se ha ingresado el numero de documento'), RC::HTTP_BAD_REQUEST);
        if(empty($date_birth))                     return $this->response(array('error'=>'No se ha ingresado la fecha de nacimiento'), RC::HTTP_BAD_REQUEST);
        if(empty($legal_address))                  return $this->response(array('error'=>'No se ha ingresado la dirección de domicilio'), RC::HTTP_BAD_REQUEST);
        if(empty($legal_locality))                 return $this->response(array('error'=>'No se ha ingresado la localidad de domicilio'), RC::HTTP_BAD_REQUEST);
        if(empty($zip_code))                       return $this->response(array('error'=>'No se ha ingresado el codigo postal'), RC::HTTP_BAD_REQUEST);
        if(empty($email))                          return $this->response(array('error'=>'No se ha ingresado el correo electrónico'), RC::HTTP_BAD_REQUEST);
        if(empty($office_address))                 return $this->response(array('error'=>'No se ha ingresado la dirección de consulta'), RC::HTTP_BAD_REQUEST);
        if(empty($office_locality))                return $this->response(array('error'=>'No se ha ingresado la localidad de consulta'), RC::HTTP_BAD_REQUEST);
        if(empty($cuit))                           return $this->response(array('error'=>'No se ha ingresado el CUIT'), RC::HTTP_BAD_REQUEST);
        if(empty($speciality_id))                  return $this->response(array('error'=>'No se ha ingresado la especialidad médica'), RC::HTTP_BAD_REQUEST);
        if(empty($type_partner))                   return $this->response(array('error'=>'No se ha ingresado el tipo de socio'), RC::HTTP_BAD_REQUEST);
        if(empty($id_medical_career))              return $this->response(array('error'=>'No se ha ingresado la categoría del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($id_payment_type))                return $this->response(array('error'=>'No se ha ingresado la forma de pago'), RC::HTTP_BAD_REQUEST);
        if(empty($date_start_activity))            return $this->response(array('error'=>'No se ha ingresado la fecha de inicio de actividad del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($iibb))                           return $this->response(array('error'=>'No se ha ingresado el numero de ingresos brutos del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($iibb_percentage))                return $this->response(array('error'=>'No se ha ingresado el porcentaje de ingresos brutos del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($iva_id))                         return $this->response(array('error'=>'Se debe indicar la situacion frente al iva del Profesional'), RC::HTTP_BAD_REQUEST);

        $gain = (boolval($gain) ? 'true' : 'false');
        if(empty($gain))
            return $this->response(array('error'=>'Se debe indicar si es necesario retenerle o no ganancia al Profesional'), RC::HTTP_BAD_REQUEST);
        $retention_vat_valid  = (boolval($retention_vat) ? 'true' : 'false');
        $retention_gain_valid = (boolval($retention_gain) ? 'true' : 'false');

        // Validate Monotributo
        if($iva_id == 6 && (empty($retention_vat_valid)) || (empty($retention_gain_valid)))
            return $this->response(array('error'=>'Se debe indicar si es necesario retener ganancia y/o aportar iva '), RC::HTTP_BAD_REQUEST);

        if($id_payment_type == 2 || $id_payment_type == 4){
            if(empty($bank_id))                 return $this->response(array('error'=>'No se ha ingresado el banco elegido por el profesional'), RC::HTTP_BAD_REQUEST);
            if(empty($account_number))          return $this->response(array('error'=>'Se debe indicar el numero de cuenta del Profesional'), RC::HTTP_BAD_REQUEST);
        }

        if($id_payment_type == 5){
            if(empty($cbu_number))              return $this->response(array('error'=>'Se debe indicar el numero de CBU del Profesional'), RC::HTTP_BAD_REQUEST);
        }

        if($id_payment_type != 1 && (empty($account_number) && empty($cbu_number) )){
            return $this->response(array('error'=>'Debe ingresar el numero de Cuenta o Numero de CBU'));
        }

        if(!$this->validator->validateDocument($document_type,$document_number))    return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), RC::HTTP_BAD_REQUEST);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))                              return $this->response(array('error'=>'El formato de email no es correcto'), RC::HTTP_BAD_REQUEST);

        //Valid if document number exist
        $error = $this->Professionals->validateData($document_number, $speciality_id, $id_category_femeba, $id_medical_career, $id_payment_type, $bank_id);
        if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), RC::HTTP_BAD_REQUEST);

        //save the Professionals
        $result = $this->Professionals->save($registration_number, $name, $last_name, $document_type, $document_number, $date_birth, $legal_address, $legal_locality, $zip_code, $phone_number, $email, $office_address, $office_locality, $cuit, $speciality_id, $type_partner, $id_category_femeba, $id_medical_career,  $id_payment_type, $bank_id, $date_start_activity, $iibb, $iibb_percentage, $gain, $iva_id, $retention_vat, $retention_gain, $account_number, $cbu_number);
        if($result != 1 )
            if(strcmp($result,"OK") != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>'Profesional creado satisfactoriamente'), RC::HTTP_OK);
    }

    public function professionals_get(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);
        
        //Validates permissions
        if(!in_array("ABMprofesionales",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        $profesionals_result = $this->Professionals->getProfessionals();
        return $this->response($profesionals_result, RC::HTTP_OK);
    }

    public function getProfessionals_get(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMprofesionales",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);

        $id = $this->get('id');
        if(empty($id)) return $this->response(array('error'=>'Falta el ID del profesional'), RC::HTTP_BAD_REQUEST);

        $professional = $this->Professionals->getProfessionalsById($id);

        if(empty($professional)){
            return $this->response(array('error'=>'No se encontro el ID del profesional'), RC::HTTP_BAD_REQUEST);
        } else {
            return $this->response($professional, RC::HTTP_OK);
        }
    }

    public function updateProfessionals_put(){
        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMprofesionales",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);
        $post = json_decode(file_get_contents('php://input'));
        $id               = (int) $this->get('id');

        $registration_number        = $post->registration_number        ?? "";
        $name                       = $post->name                       ?? "";
        $last_name                  = $post->last_name                  ?? "";
        $document_type              = $post->document_type              ?? "";
        $document_number            = $post->document_number            ?? "";
        $date_birth                 = $post->date_birth                 ?? "";
        $legal_address              = $post->legal_address              ?? "";
        $legal_locality             = $post->legal_locality             ?? "";
        $zip_code                   = $post->zip_code                   ?? "";
        $phone_number               = $post->phone_number               ?? "";
        $email                      = $post->email                      ?? "";
        $office_address             = $post->office_address             ?? "";
        $office_locality            = $post->office_locality            ?? "";
        $id_fiscal_data             = $post->id_fiscal_data             ?? "";
        $speciality_id              = $post->speciality_id              ?? "";
        $type_partner               = $post->type_partner               ?? "";
        $id_category_femeba         = $post->id_category_femeba         ?? "";
        $id_medical_career          = $post->id_medical_career          ?? "";
        $id_payment_type            = $post->id_payment_type            ?? "";
        $bank_id                    = $post->bank_id                    ?? "";
        $cuit                       = $post->cuit                       ?? "";
        $date_start_activity        = $post->date_start_activity        ?? "";
        $iibb                       = $post->iibb                       ?? "";
        $iibb_percentage            = $post->iibb_percentage            ?? "";
        $iva_id                     = $post->iva_id                     ?? "";
        $retention_vat              = $post->retention_vat              ?? "";
        $retention_gain             = $post->retention_gain             ?? "";
        $account_number             = $post->account_number             ?? "";
        $cbu_number                 = $post->cbu_number                 ?? "";

        if(!isset($post->gain))                   return $this->response(array('error'=>'Se debe indicar si es necesario retenerle o no ganancia al Profesional'), RC::HTTP_BAD_REQUEST);
        $gain                       = $post->gain                       ?? "";

        if(empty($registration_number))            return $this->response(array('error'=>'No se ha ingresado numero de matricula'), RC::HTTP_BAD_REQUEST);
        if(empty($name))                           return $this->response(array('error'=>'No se ha ingresado el nombre'), RC::HTTP_BAD_REQUEST);
        if(empty($last_name))                      return $this->response(array('error'=>'No se ha ingresado el apellido'), RC::HTTP_BAD_REQUEST);
        if(empty($document_type))                  return $this->response(array('error'=>'No se ha ingresado el tipo de documento'), RC::HTTP_BAD_REQUEST);
        if(empty($document_number))                return $this->response(array('error'=>'No se ha ingresado el numero de documento'), RC::HTTP_BAD_REQUEST);
        if(empty($date_birth))                     return $this->response(array('error'=>'No se ha ingresado la fecha de nacimiento'), RC::HTTP_BAD_REQUEST);
        if(empty($legal_address))                  return $this->response(array('error'=>'No se ha ingresado la dirección de domicilio'), RC::HTTP_BAD_REQUEST);
        if(empty($legal_locality))                 return $this->response(array('error'=>'No se ha ingresado la localidad de domicilio'), RC::HTTP_BAD_REQUEST);
        if(empty($zip_code))                       return $this->response(array('error'=>'No se ha ingresado el codigo postal'), RC::HTTP_BAD_REQUEST);
        if(empty($email))                          return $this->response(array('error'=>'No se ha ingresado el correo electrónico'), RC::HTTP_BAD_REQUEST);
        if(empty($office_address))                 return $this->response(array('error'=>'No se ha ingresado la dirección de consulta'), RC::HTTP_BAD_REQUEST);
        if(empty($office_locality))                return $this->response(array('error'=>'No se ha ingresado la localidad de consulta'), RC::HTTP_BAD_REQUEST);
        if(empty($id_fiscal_data))                 return $this->response(array('error'=>'Datos incompletos'), RC::HTTP_BAD_REQUEST);
        if(empty($speciality_id))                  return $this->response(array('error'=>'No se ha ingresado la especialidad médica'), RC::HTTP_BAD_REQUEST);
        if(empty($type_partner))                   return $this->response(array('error'=>'No se ha ingresado el tipo de socio'), RC::HTTP_BAD_REQUEST);
        if(empty($id_medical_career))              return $this->response(array('error'=>'No se ha ingresado la categoría del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($id_payment_type))                return $this->response(array('error'=>'No se ha ingresado la forma de pago'), RC::HTTP_BAD_REQUEST);
        if(empty($bank_id))                        return $this->response(array('error'=>'No se ha ingresado el banco elegido por el profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($date_start_activity))            return $this->response(array('error'=>'No se ha ingresado la fecha de inicio de actividad del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($iibb))                           return $this->response(array('error'=>'No se ha ingresado el numero de ingresos brutos del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($iibb_percentage))                return $this->response(array('error'=>'No se ha ingresado el porcentaje de ingresos brutos del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($iva_id))                         return $this->response(array('error'=>'Se debe indicar la situacion frente al iva del Profesional'), RC::HTTP_BAD_REQUEST);
        if(empty($cuit))                           return $this->response(array('error'=>'No se ha ingresado el numero de CUIT'), RC::HTTP_BAD_REQUEST);

        $gain = (boolval($gain) ? 'true' : 'false');
        if(empty($gain))
            return $this->response(array('error'=>'Se debe indicar si es necesario retenerle o no ganancia al Profesional'), RC::HTTP_BAD_REQUEST);
        $retention_vat_valid  = (boolval($retention_vat) ? 'true' : 'false');
        $retention_gain_valid = (boolval($retention_gain) ? 'true' : 'false');

        // Validate Monotributo
        if($iva_id == 6 && (empty($retention_vat_valid)) || (empty($retention_gain_valid)))
            return $this->response(array('error'=>'Se debe indicar si es necesario retener ganancia y/o aportar iva '), RC::HTTP_BAD_REQUEST);

        if($id_payment_type == 2 || $id_payment_type == 4){
            if(empty($bank_id))                 return $this->response(array('error'=>'No se ha ingresado el banco elegido por el profesional'), RC::HTTP_BAD_REQUEST);
            if(empty($account_number))          return $this->response(array('error'=>'Se debe indicar el numero de cuenta del Profesional'), RC::HTTP_BAD_REQUEST);
        }

        if($id_payment_type == 5){
            if(empty($cbu_number))              return $this->response(array('error'=>'Se debe indicar el numero de CBU del Profesional'), RC::HTTP_BAD_REQUEST);
        }

        if($id_payment_type != 1 && (empty($account_number) && empty($cbu_number) )){
            return $this->response(array('error'=>'Debe ingresar el numero de Cuenta o Numero de CBU'));
        }
        if(!$this->validator->validateDocument($document_type,$document_number))    return $this->response(array('error'=>'Se ha ingresado mal el tipo y/o numero de documento'), RC::HTTP_BAD_REQUEST);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))                              return $this->response(array('error'=>'El formato de email no es correcto'), RC::HTTP_BAD_REQUEST);

        //Valid Document number
        $error = $this->Professionals->validateDataUpdate($id, $document_number);
        if(strcmp($error,"OK") != 0) return $this->response(array('error'=>$error), RC::HTTP_BAD_REQUEST);

        if(!$this->Professionals->update($id, $registration_number, $name, $last_name, $document_type, $document_number, $date_birth, $legal_address, $legal_locality, $zip_code, $phone_number, $email, $office_address, $office_locality, $id_fiscal_data, $speciality_id, $type_partner, $id_category_femeba, $id_medical_career,  $id_payment_type, $bank_id, $date_start_activity, $iibb, $iibb_percentage, $gain, $iva_id, $retention_vat, $retention_gain, $cuit, $account_number, $cbu_number)){
            return $this->response(array('msg'=>'Profesional actualizado de forma correcta'), RC::HTTP_OK);
        }else{
            return $this->response(array('error'=>'Error de base de datos'), RC::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeProfessional_delete(){

        //Validate Token.
        if($this->token_valid->status != "ok") return $this->response(array('error'=>$this->token_valid->message), RC::HTTP_UNAUTHORIZED);

        //Validates permissions
        if(!in_array("ABMprofesionales",$this->token_valid->permissions))
            return $this->response(array('error'=>'No tiene los permisos para realizar esta accion'), RC::HTTP_FORBIDDEN);
        $id = (int) $this->get('id');

        if($this->Professionals->delete($id, $this->token_valid->user_id )){
            return $this->response(array('msg'=>'Profesional eliminado satisfactoriamente'), RC::HTTP_OK);
        }else{
            return $this->response(array('error'=>'Error al intentar eliminar profesional'), RC::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}






