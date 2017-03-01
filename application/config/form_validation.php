<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 23/02/2017
 * Time: 17:52
 */


$config = array(
    'login_validate' => array(
        array(
            'field' => 'dni',
            'label' => 'DNI',
            'rules' => 'required',
            "errors" => array('required' => " Debe ingresar numero de  %s.")

        ),
        array(
            'field' => 'clave',
            'label' => 'Clave',
            'rules' => 'required',
            "errors" => array('required' => "Debe ingresar  su  contraseña.")
        )
    ),
);


