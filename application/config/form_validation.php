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
    ],

    /**
     * Validation of Bill
     */
    'BillController/bill' => [
        [
            'field' => 'id_medical_insurance',
            'label' => 'Obra Social',
            'rules' => 'required|custom_validation',
            'errors' => ['required' => " Debe ingresar número de %s."]
        ],
        [
            'field' => 'date_billing',
            'label' => 'Perido de Facturación',
            'rules' => 'required',
            'errors' => ['required' => " Debe ingresar el %s."]
        ],
        [
            'field' => 'branch_officce',
            'label' => 'Sucrusal',
            'rules' => 'required',
            'errors' => ['required' => " Debe ingresar la %s."]
        ]

    ]

];
