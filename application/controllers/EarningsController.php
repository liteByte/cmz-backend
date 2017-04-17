<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/controllers/AuthController.php';

// use namespace
use Restserver\Libraries\REST_Controller as RC;

class EarningsController extends AuthController{

    protected $access = "ABMganancia";
    private  $token_valid;
    private $msgSucces = "Información de Administración de ganancias se guardo satisfactoriamente ";
    private $msgRemove = "Información de Administración de ganancias se elimino satisfactoriamente ";
    private $msgEmpty = "No hay existe información ";

    function __construct(){
        parent::__construct();
        $this->token_valid = $this->validateToken();
        $this->load->model('Earnings');
    }


    public function earnings_post(){

        $post = json_decode(file_get_contents('php://input'));
        $since = $post->since               ?? "";
        $until = $post->until               ?? "";
        $fixed = $post->fixed               ?? "";
        $percentage = $post->percentage     ?? "";
        $minimun   = $post->minimun        ?? "";
        $impuni    = $post->impuni          ?? "";


        if($since < 0 || !is_numeric($since))           return $this->response(['error'=>'El Valor de \'Desde\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($until < 0 || $until < $since)               return $this->response(['error'=>'El Valor de \'Hasta\' debe ser mayor a 0 y al valor de \'Desde\' '], RC::HTTP_BAD_REQUEST);
        if($fixed < 0 || !is_numeric($fixed))           return $this->response(['error'=>'El Valor de \'Fijo\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($percentage < 0 || !is_numeric($percentage)) return $this->response(['error'=>'El Valor de \'Porcentaje\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($minimun < 0 ||!is_numeric($minimun))        return $this->response(['error'=>'El Valor de \'Minumun\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($impuni < 0 || !is_numeric($impuni))         return $this->response(['error'=>'El Valor de \'Impuni\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);


        $result  = $this->Earnings->save($post);
        if($result != 1 )
            if(strcmp($result,1) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>$this->msgSucces), RC::HTTP_OK);
    }

    public function earnings_get(){

        $result = $this->Earnings->getAll();

        if(empty($result)){
            return $this->response(array('error'=>$this->msgEmpty), RC::HTTP_FORBIDDEN);
        }else{
            return $this->response($result, RC::HTTP_OK);
        }
    }

    public function getEarnings_get(){

        $id = $this->get('id');

        $result = $this->Earnings->getById($id);

        if(!$result){
            return $this->response(array('error'=>$this->msgEmpty), RC::HTTP_NOT_FOUND);
        }else {
            return $this->response($result, RC::HTTP_OK);
        }
    }

    public function earnings_put(){
        $post = json_decode(file_get_contents('php://input'));
        $id = $this->get('id');

        $since = $post->since               ?? "";
        $until = $post->until               ?? "";
        $fixed = $post->fixed               ?? "";
        $percentage = $post->percentage     ?? "";
        $minimun   = $post->minimun        ?? "";
        $impuni    = $post->impuni          ?? "";


        if($since < 0 || !is_numeric($since))           return $this->response(['error'=>'El Valor de \'Desde\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($until < 0 || $until < $since)               return $this->response(['error'=>'El Valor de \'Hasta\' debe ser mayor a 0 y al valor de \'Desde\' '], RC::HTTP_BAD_REQUEST);
        if($fixed < 0 || !is_numeric($fixed))           return $this->response(['error'=>'El Valor de \'Fijo\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($percentage < 0 || !is_numeric($percentage)) return $this->response(['error'=>'El Valor de \'Porcentaje\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($minimun < 0 ||!is_numeric($minimun))        return $this->response(['error'=>'El Valor de \'Minumun\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);
        if($impuni < 0 || !is_numeric($impuni))         return $this->response(['error'=>'El Valor de \'Impuni\' no puede ser menor a 0'], RC::HTTP_BAD_REQUEST);

        $result  = $this->Earnings->update($id, $post);

        if($result != 1 )
            if(strcmp($result,1) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>$this->msgSucces), RC::HTTP_OK);
    }


    public function earnings_delete(){
        $id = $this->get('id');

        $result = $this->Earnings->delete($id);

        if($result != 1)
            if(strcmp($result,1) != 0) return $this->response(array('error'=>$result), RC::HTTP_BAD_REQUEST);
        return $this->response(array('msg'=>$this->msgSucces), RC::HTTP_OK);
    }
}


