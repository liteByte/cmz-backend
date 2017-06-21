<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_table_pay_receipt extends CI_Migration{

    public function up(){

        $columnasAdicionales = array(
            'state' => array(
                'type'           => 'INT',
                'constraint'     =>  1,
                'null'           =>  FALSE,
                'comment'        => '1-Generado (pendiente de liquidar)'
            )
        );

        $this->dbforge->add_column('pay_receipt', $columnasAdicionales);

    }


    public function down(){



    }









}
