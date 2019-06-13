<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Disciple_Tools_Dashboard{
    public function __construct() {}



    public static function get_data(){

        $counts = Disciple_Tools_Contacts::get_count_of_contacts();
        return [
            "active_contacts" => $counts["active"],
            "accept_needed" => $counts["needs_accepted"],
            "update_needed" => $counts["update_needed"]

        ];
    }
}
