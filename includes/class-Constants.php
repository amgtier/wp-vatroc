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

class VATROC_Constants {
    public static $ATC = "atc";

    public static $STAFF = "staff";

    public static $meta_prefix = "vatroc_";

    public static $vatsim_rating = array(
        "0" => "Pilot",
        "1" => "S1",
        "2" => "S2",
        "3" => "S+",
        "4" => "C1",
        "6" => "C3",
        "7" => "I1",
        "8" => "I+"
    );

    public static $atc_position = array(
        "0" => "Pilot",
        "1" => "Applicant",
        "2" => "GND Sim",
        "3" => "GND OJT",
        "4" => "GND",
        "6" => "TWR OJT",
        "7" => "TWR",
        "8" => "APP OJT",
        "9" => "APP",
        "10" => "CTR OJT",
        "11" => "CTR"
    );
};
