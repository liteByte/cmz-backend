<?php

defined("BASEPATH") or exit("No direct script access allowed");

class UploadExcelController extends CI_Controller
{

    public function test()    {
        $this->load->library('excel');
        $file = "aa";/*Obtener archivo subido*/

        if(!$file) {
            if (((($_FILES["file"]["type"] == "application/vnd.ms-excel"))
                    || ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
                && ($_FILES["file"]["size"] < 10000000)
            ) {
                if ($_FILES["file"]["error"] > 0) {
                    //Devolver objeto con error
                    echo "ERROR formato o tamaño de archivo invalido";
                    die;
                } else {
                    //carpeta donde se guardan todos los archivos procesados
                    $id_usuario = 1;
                    $archivo = "upload_prestaciones/id_os_" . uniqid(mt_rand(), true) . "_" . $_FILES["file"]["name"];
                    move_uploaded_file($_FILES["file"]["tmp_name"], $archivo);
                    $log_interfaces_name = basename($_SERVER['SCRIPT_NAME']);
                    $log_interfaces_ts = date("Y-m-d H:i:s");
                    $log_interfaces_data = $archivo;
                    //INSERTAR EN AUDITORIA EL ARCHIVO PROCESADO; QUIEN LO PROCESO; CUANDO Y DIRECCION DEL ARCHIVO EN EL SERVER
                    //("INSERT INTO auditoria (id_usuario, interfaz, fecha_ejecucion, valor) VALUES
                      //          ($id_usuario, '$log_interfaces_name', '$log_interfaces_ts', '$log_interfaces_data')");
                }
            }
            $FileType = PHPExcel_IOFactory::identify($archivo);

            $objReader = PHPExcel_IOFactory::createReader($FileType);
            switch ($FileType) {
                case 'Excel2007':
                case 'Excel2003XML':
                case 'Excel5':
                case 'OOCalc':
                case 'SYLK':
                    break;
            }

            $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
            $cacheSettings = array( ' memoryCacheSize ' => '256MB');
            PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($archivo);
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getSheet(0);
            $highestRow = $objWorksheet->getHighestRow();

            $posicion_inicial = 1; //Se obtiene de las configuraciones
            $columna_codigo_prestacion = 1; //Se obtiene de las configuraciones
            for ($row = $posicion_inicial; $row <= $highestRow; $row++){
                $codigo_prestacion = $objWorksheet->getCellByColumnAndRow($columna_codigo_prestacion, $row)->getValue();

                //etc

            }

        }


    }


}

