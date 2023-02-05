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
    public static $ATC_LOCAL = "atc_local";
    public static $ATC_VISITING = "atc_visiting";
    public static $ATC_SOLO = "atc_solo";
    public static $ATC_TIMELINE = "atc_timeline";
    public static $STAFF = "staff";
    public static $PILOT = "pilot";
    public static $meta_prefix = "vatroc_";
    public static $session_meta_prefix = "vatroc_sess_";
    public static $session_t_meta_prefix = "vatroc_sess_t_";
    public static $icao_prefix = "RC";
    public static $admin_options = "manage_options";
    public static $atc_options = "publish_posts";
    public static $ins_options = "edit_users";

    public static $vatsim_rating = array(
        "0" => "Suspended",
        "1" => "Pilot",
        "2" => "S1",
        "3" => "S2",
        "4" => "S+",
        "5" => "C1",
        "7" => "C3",
        "8" => "I1",
        "9" => "I+"
    );

    public static $atc_position = array(
        "0" => "Applicant",
        "1" => "DEL OJT",
        "2" => "DEL",
        "3" => "GND OJT",
        "4" => "GND",
        "6" => "TWR OJT",
        "7" => "TWR",
        "8" => "APP OJT",
        "9" => "APP",
        "10" => "CTR OJT",
        "11" => "CTR"
    );

    public static $atc_dates_offline = array(
        "application" => "Application",
        "enrolled" => "Trainee Enrolled",
        "exam" => "Exam",
        "del_sim" => "DEL SIM",
        "gnd_sim" => "GND SIM",
        "twr_sim" => "TWR SIM",
        "app_sim" => "APP SIM",
        "ctr_sim" => "CTR SIM",
    );

    public static $atc_dates_in_sess = array(
        "del_ojt" => "DEL OJT",
        "del_cpt" => "DEL CPT",
        "gnd_ojt" => "GND OJT",
        "gnd_cpt" => "GND CPT",
        "twr_ojt" => "TWR OJT",
        "twr_cpt" => "TWR CPT",
        "app_ojt" => "APP OJT",
        "app_cpt" => "APP CPT",
        "ctr_ojt" => "CTR OJT",
        "ctr_cpt" => "CTR CPT",
    );
};
