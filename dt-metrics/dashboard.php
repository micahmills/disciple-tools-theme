<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Disciple_Tools_Dashboard{
    public function __construct() {}



    public static function get_data(){

        $counts = Disciple_Tools_Contacts::get_count_of_contacts();
        $to_accept = Disciple_Tools_Contacts::search_viewable_contacts( [
            'overall_status' => [ 'assigned' ],
            'assigned_to'    => [ 'me' ]
        ] );
        $update_needed = Disciple_Tools_Contacts::search_viewable_contacts( [
            'requires_update' => [ "true" ],
            'assigned_to'     => [ 'me' ]
        ] );

        $seeker_path_personal = self::query_my_contacts_progress();
        $seeker_path = self::query_project_contacts_progress();
        $milestones = self::milestones();

        $test = "";
        return [
            "active_contacts" => $counts["active"],
            "accept_needed_count" => $counts["needs_accepted"],
            "update_needed_count" => $counts["update_needed"],
            "accept_needed" => $to_accept,
            "update_needed" => $update_needed,
            "benchmarks" => self::get_personal_benchmarks(),
            "seeker_path" => $seeker_path,
            "seeker_path_personal" => $seeker_path_personal,
            "milestones" => $milestones
        ];
    }

    private static function get_personal_benchmarks(){
        global $wpdb;
        $thirty_days_ago = time() - 30 * 24 * 60 * 60;
        $sixty_days_ago = $thirty_days_ago - 30 * 24 * 60 * 60;

        $contacts_current = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(DISTINCT(object_id)) FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts' 
            AND meta_key = 'assigned_to'
            AND hist_time >= %s 
            AND meta_value = %s
        ", $thirty_days_ago, "user-" . get_current_user_id() )
        );

        $contacts_previous = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(DISTINCT(object_id)) FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts' 
            AND meta_key = 'assigned_to'
            AND hist_time >= %s 
            AND hist_time < %s 
            AND meta_value = %s
        ", $sixty_days_ago, $thirty_days_ago, "user-" . get_current_user_id() )
        );

        $met_current = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(DISTINCT(object_id)) FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts' 
            AND meta_key = 'seeker_path'
            AND hist_time >= %s 
            AND meta_value = 'met'
            AND user_id = %s
        ", $thirty_days_ago, get_current_user_id() )
        );

        $met_previous = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(DISTINCT(object_id)) FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts' 
            AND meta_key = 'seeker_path'
            AND hist_time >= %s 
            AND hist_time < %s
            AND meta_value = 'met'
            AND user_id = %s
        ", $sixty_days_ago, $thirty_days_ago, get_current_user_id() )
        );

        $milestones_current = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(DISTINCT(object_id)) FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts' 
            AND meta_key = 'milestones'
            AND hist_time >= %s 
            AND meta_value != 'value_deleted'
            AND user_id = %s
        ", $thirty_days_ago, get_current_user_id() )
        );

        $milestones_previous = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(*) FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts' 
            AND meta_key = 'milestones'
            AND hist_time >= %s 
            AND hist_time < %s
            AND meta_value != 'value_deleted'
            AND user_id = %s
        ", $sixty_days_ago, $thirty_days_ago, get_current_user_id() )
        );

        return [
            "contacts" => [
                "previous" => $contacts_previous,
                "current" => $contacts_current,
            ],
            "meetings" => [
                "previous" => $met_previous,
                "current" => $met_current
            ],
            "milestones" => [
                "previous" => $milestones_previous,
                "current" => $milestones_current
            ]
        ];
    }

    public static function query_my_contacts_progress( $user_id = null ) {
        global $wpdb;
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $defaults = [];
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $contact_fields["seeker_path"]["default"];
        foreach ( $seeker_path_options as $key => $option ) {
            $defaults[$key] = [
                'label' => $option["label"],
                'count' => 0,
            ];
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT b.meta_value as seeker_path, count( a.ID ) as count
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
               JOIN $wpdb->postmeta as c
                 ON a.ID=c.post_id
                    AND c.meta_key = 'assigned_to'
                    AND c.meta_value = %s
               JOIN $wpdb->postmeta as d
                 ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'active'
             WHERE a.post_status = 'publish'
              AND a.post_type = 'contacts'
              AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                )
             GROUP BY b.meta_value
        ",
        'user-'. $user_id ), ARRAY_A );

        $query_results = [];

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( isset( $defaults[$result['seeker_path']] ) ) {
                    $query_results[$result['seeker_path']] = [
                        'label' => $defaults[$result['seeker_path']]['label'],
                        'count' => intval( $result['count'] ),
                    ];
                }
            }
        }
        $query_results = wp_parse_args( $query_results, $defaults );
        $res = [];
        foreach ( $query_results as $r ){
            $res[] = [
                "label" => $r['label'],
                "value" => $r["count"]
            ];
        }

        return $res;
    }


    public static function query_project_contacts_progress() {
        global $wpdb;


        $results = $wpdb->get_results( "
            SELECT b.meta_value as seeker_path, count( a.ID ) as count
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
               JOIN $wpdb->postmeta as d
                 ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'active'
             WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                )
             GROUP BY b.meta_value
        ", ARRAY_A );

        $query_results = [];

        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $contact_fields["seeker_path"]["default"];

        foreach ( $seeker_path_options as $seeker_path_key => $seeker_path_option ){
            $added = false;
            foreach ( $results as $result ) {
                if ( $result["seeker_path"] == $seeker_path_key ){
                    $query_results[] = [
                        'key' => $seeker_path_key,
                        'label' => $seeker_path_option['label'],
                        'value' => intval( $result['count'] )
                    ];
                    $added = true;
                }
            }
            if ( !$added ){
                $query_results[] = [
                    'key' => $seeker_path_key,
                    'label' => $seeker_path_option['label'],
                    'value' => 0
                ];
            }
        }

        return $query_results;
    }

    public static function milestones( $start = null, $end = null ){
        global $wpdb;
        if ( empty( $start ) ){
            $start = 0;
        }
        if ( empty( $end ) ){
            $end = time();
        }

        $res = $wpdb->get_results( $wpdb->prepare( "
            SELECT COUNT( DISTINCT(log.object_id) ) as `value`, log.meta_value as milestones
            FROM $wpdb->dt_activity_log log
            INNER JOIN $wpdb->posts post 
            ON (
                post.ID = log.object_id
                AND post.post_type = 'contacts'
                AND post.post_status = 'publish'
            )
            INNER JOIN $wpdb->postmeta pm
            ON (
                pm.post_id = post.ID
                AND pm.meta_key = 'milestones'
                AND pm.meta_value = log.meta_value
            )
            WHERE log.meta_key = 'milestones'
            AND log.object_type = 'contacts'
            AND log.hist_time > %s
            AND log.hist_time < %s
            GROUP BY log.meta_value
        ", $start, $end ), ARRAY_A );

        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $milestones_options = $field_settings["milestones"]["default"];
        $milestones_data = [];

        foreach ( $milestones_options as $option_key => $option_value ){
            $milestones_data[$option_value["label"]] = 0;
            foreach ( $res as $r ){
                if ( $r["milestones"] === $option_key ){
                    $milestones_data[$option_value["label"]] = $r["value"];
                }
            }
        }
        $return = [];
        foreach ( $milestones_data as $k => $v ){
            $return[] = [
                "milestones" => $k,
                "value" => (int) $v
            ];
        }

        return $return;
    }

}
