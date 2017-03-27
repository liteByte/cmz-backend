<?php

$config = [
    'login_validate' => [
        [
            'field' => 'document_number',
            'label' => 'DNI',
            'rules' => 'required',
            'errors' => ['required' => " Debe ingresar número de %s."]
        ],
        [
            'field' => 'password',
            'label' => 'Clave',
            'rules' => 'required',
            'errors' => ['required' => "Debe ingresar su contraseña."]
        ]
    ]
];
