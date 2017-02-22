<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 21/02/2017
 * Time: 13:20
 */


class Migrate extends CI_Controller {

    public function index()    {

        $this->load->library('migration');
        if ( ! $this->migration->current()) {
            show_error($this->migration->error_string());
        } else {
            echo "Migration Worked";
        }
    }
}
