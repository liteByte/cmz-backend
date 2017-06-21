<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_table_professionals extends CI_Migration{

    public function up(){

        $columnasAdicionales = array(
            'osde' => array(
                'type'           => 'VARCHAR',
                'constraint'     =>  6,
                'null'           =>  TRUE,
                'comment'        => 'Matricula del profesional para los archivos que manda osde'
            )
        );

        $this->dbforge->add_column('professionals', $columnasAdicionales);

    }


    public function down(){



    }









}
