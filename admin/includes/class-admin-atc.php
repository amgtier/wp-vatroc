<?php
/**
 * VATROC Admin Dashboard
 *
 * @class VATROC_AdminDashboard
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VATROC_ATC {


    public function __construct() {
    }

    public function output() {
        printf("<h1>Hello dashboard;</h1>");
    }

};

return new VATROC_ATC();
