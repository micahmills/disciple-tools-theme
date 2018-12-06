<?php

Disciple_Tools_Metrics_Project::instance();
class Disciple_Tools_Metrics_Project extends Disciple_Tools_Metrics_Hooks_Base
{
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        if ( !$this->has_permission() ){
            return;
        }
        $url_path = dt_get_url_path();
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/project' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/project'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="">' .  esc_html__( 'Project', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="project-menu">
                    <li><a href="'. site_url( '/metrics/project/' ) .'#project_overview" onclick="project_overview()">'. esc_html__( 'Overview', 'disciple_tools' ) .'</a></li>
                    <li><a href="'. site_url( '/metrics/project/' ) .'#project_critical_path" onclick="project_critical_path()">'. esc_html__( 'Critical Path', 'disciple_tools' ) .'</a></li>
                    <li><a href="" >' .  esc_html__( 'Trees', 'disciple_tools' ) . '</a>
                        <ul class="menu vertical nested" id="trees">
                            <li><a href="'. site_url( '/metrics/project/' ) .'#group_tree" onclick="project_group_tree()">'. esc_html__( 'Group Tree', 'disciple_tools' ) .'</a></li>
                            <li><a href="'. site_url( '/metrics/project/' ) .'#baptism_tree" onclick="project_baptism_tree()">'. esc_html__( 'Baptism Tree', 'disciple_tools' ) .'</a></li>
                            <li><a href="'. site_url( '/metrics/project/' ) .'#coaching_tree" onclick="project_coaching_tree()">'. esc_html__( 'Coaching Tree', 'disciple_tools' ) .'</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            ';
        return $content;
    }

    public function scripts() {
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_enqueue_script( 'dt_metrics_project_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-project.js', [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-project.js' ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_stylesheet_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {

        /**
         * Apply Filters before final enqueue. This provides opportunity for complete override or modification of chart.
         */

        return [
            'translations' => [
                'title_overview' => __( 'Project Overview', 'disciple_tools' ),
                'title_timeline' => __( 'Timeline Activity', 'disciple_tools' ),
                'title_critical_path' => __( 'Critical Path', 'disciple_tools' ),
                'title_outreach' => __( 'Outreach', 'disciple_tools' ),
                'title_follow_up' => __( 'Follow Up', 'disciple_tools' ),
                'title_training' => __( 'Training', 'disciple_tools' ),
                'title_contacts' => __( 'Contacts', 'disciple_tools' ),
                'title_groups' => __( 'Groups', 'disciple_tools' ),
                'title_multiplication' => __( 'Multiplication', 'disciple_tools' ),
                'title_all_contacts' => __( 'All Contacts', 'disciple_tools' ),
                'title_active_contacts' => __( 'Active Contacts', 'disciple_tools' ),
                'title_waiting_on_accept' => __( 'Waiting on Accept', 'disciple_tools' ),
                'title_waiting_on_update' => __( 'Waiting on Update', 'disciple_tools' ),
                'title_project_groups' => __( 'Project Groups', 'disciple_tools' ),
                'title_total_groups' => __( 'Total Groups', 'disciple_tools' ),
                'title_needs_training' => __( 'Needs Training', 'disciple_tools' ),
                'title_fully_practicing' => __( 'Fully Practicing', 'disciple_tools' ),
                'title_generations' => __( 'Group and Church Generations', 'disciple_tools' ),
                'title_group_types' => __( 'Groups Types', 'disciple_tools' ),
                'title_group_tree' => __( 'Group Generation Tree', 'disciple_tools' ),
                'title_baptism_tree' => __( 'Baptism Generation Tree', 'disciple_tools' ),
                'title_coaching_tree' => __( 'Coaching Generation Tree', 'disciple_tools' ),
                'label_number_of_contacts' => strtolower( __( 'number of contacts', 'disciple_tools' ) ),
                'label_follow_up_progress' => __( 'Follow-up progress of current active contacts', 'disciple_tools' ),
                'label_group_needs_training' => __( 'Active Groups Health Metrics', 'disciple_tools' ),
                'label_groups' => strtolower( __( 'groups', 'disciple_tools' ) ),
                'label_generations' => strtolower( __( 'generations', 'disciple_tools' ) ),
                'label_groups_by_type' => strtolower( __( 'groups by type', 'disciple_tools' ) ),
                'label_stats_as_of' => strtolower( __( 'stats as of', 'disciple_tools' ) ),
                'label_select_year' => __( 'Select All time or a specific year to display', 'disciple_tools' ),
                'label_all_time' => __( 'All time', 'disciple_tools' ),
                'label_group_types' => __( 'Group Types', 'disciple_tools' ),
            ],
            'hero_stats' => self::chart_project_hero_stats(),
            'critical_path' => self::chart_critical_path( dt_date_start_of_year(), dt_date_end_of_year() ),
            'contacts_progress' => self::chart_contacts_progress( 'project' ),
            'group_types' => self::chart_group_types( 'project' ),
            'group_health' => self::chart_group_health( 'project' ),
            'group_generations' => self::chart_group_generations( 'project' ),
            'group_generation_tree' => $this->get_group_generations_tree(),
            'baptism_generation_tree' => $this->get_baptism_generations_tree(),
            'coaching_generation_tree' => $this->get_coaching_generations_tree(),
        ];
    }

    /**
     * API Routes
     */
    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/critical_path_by_year/(?P<id>[\w-]+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_by_year' ],
                ],
            ]
        );
    }

    public function critical_path_by_year( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( "critical_path_by_year", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            if ( $params['id'] == 'all'){
                $start = 0;
                $end = PHP_INT_MAX;
            } else {
                $year = (int) $params['id'];
                $start = DateTime::createFromFormat( "Y-m-d", $year . '-01-01' )->getTimestamp();
                $end = DateTime::createFromFormat( "Y-m-d", ( $year + 1 ) . '-01-01' )->getTimestamp();
            }
            $result = Disciple_Tools_Metrics_Hooks_Base::chart_critical_path( $start, $end );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "critical_path_by_year", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    public function get_group_generations_tree(){
        $query = dt_queries()->tree( 'tree_group_all' );
        $menu_data = $this->prepare_menu_array( $query );
        return $this->build_menu( 0, $menu_data, 0 );;
    }

    public function get_baptism_generations_tree(){
        $query = dt_queries()->tree( 'tree_baptisms_all' );
        $menu_data = $this->prepare_menu_array( $query );
        return $this->build_menu( 0, $menu_data, 0 );
    }

    public function get_coaching_generations_tree(){
        $query = dt_queries()->tree( 'tree_coaching_all' );
        $menu_data = $this->prepare_menu_array( $query );
        return $this->build_menu( 0, $menu_data, 0 );
    }

    /**
     * Prepares the id, parent_id list into a nested array
     *
     * @param $query
     *
     * @return array
     */
    public function prepare_menu_array( $query) {
        // prepare special array with parent-child relations
        $menu_data = array(
            'items' => array(),
            'parents' => array()
        );

        foreach ( $query as $menu_item )
        {
            $menu_data['items'][$menu_item['id']] = $menu_item;
            $menu_data['parents'][$menu_item['parent_id']][] = $menu_item['id'];
        }
        return $menu_data;
    }

    /**
     * Recursively builds html tree from nested array data.
     *
     * @param $parent_id
     * @param $menu_data
     * @param $gen
     *
     * @return string
     */
    public function build_menu( $parent_id, $menu_data, $gen) {
        $html = '';

        if (isset( $menu_data['parents'][$parent_id] ))
        {
            $html = '<ul class="ul-gen-'.$gen.'">';
            $gen++;
            foreach ($menu_data['parents'][$parent_id] as $item_id)
            {
                $html .= '<li class="gen-node li-gen-'.$gen.'">';
                $html .= '('.$gen.') ';
                $html .= '<strong><a href="'. site_url( "/groups/" ).$item_id.'">'. $menu_data['items'][$item_id]['name'] . '</a></strong><br>';

                // find childitems recursively
                $html .= $this->build_menu( $item_id, $menu_data, $gen );

                $html .= '</li>';
            }
            $html .= '</ul>';

        }
        return $html;
    }


}

