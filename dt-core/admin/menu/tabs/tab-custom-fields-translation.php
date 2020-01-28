<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Tab_Custom_Translations
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Custom_Translations
 */
class Disciple_Tools_Tab_Custom_Translations extends Disciple_Tools_Abstract_Menu_Base
{

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Custom Translations', 'disciple_tools' ), __( 'Custom Translations', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-translations', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=custom-translations"
           class="nav-tab <?php echo esc_html( $tab == 'custom-translations' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Custom Translations' ) ?>
        </a>
        <?php
    }

    private function get_post_fields( $post_type ){
        if ( $post_type === "groups" ){
            return Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( null, null, true, false );
        } elseif ( $post_type === "contacts" ){
            return Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( null, null, true, false );
        } else {
            $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type, false );
            return isset( $post_settings["fields"] ) ? $post_settings["fields"] : null;
        }
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        // dt_write_log( "Post" );
        // dt_write_log( $_POST );
        if ( isset( $_POST['field_select_nonce'] ) ){
            if ( !wp_verify_nonce( sanitize_key( $_POST['field_select_nonce'] ), 'field_select' ) ) {
                return;
            }
            if ( isset( $_POST["show_add_new_field"] ) ){
                $show_add_field = true;
            } else if ( !empty( $_POST["field-select"] ) ){
                $field = explode( "_", sanitize_text_field( wp_unslash( $_POST["field-select"] ) ), 2 );
                $field_key = $field[1];
                $post_type = $field[0];
            }
        }


        if ( 'custom-translations' == $tab ) :
            $field_key = false;
            $post_type = null;
            $this->template( 'begin' );



            $this->box( 'top', __( 'All Custom Fields, Tiles, and Lists for Translation', 'disciple_tools' ) );
            $this->list_custom_terms();
            $this->box( 'bottom' );

            $this->template( 'right_column' );

            $this->template( 'end' );
        endif;
    }

    private function list_custom_terms(){
        global $wp_post_types;
        $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
        $available_translations = dt_get_available_languages();
        $all_custom_terms = [];

        echo '<table class="widefat">';
        foreach ( $post_types as $post_type ){ ?>
        <form method="post" name="field_translation_form">
            <thead>
            <tr>
            <th colspan="11"><h3><?php echo esc_html( $post_type ); ?></h3></th>
            </tr>
            <tr>
            <th><?php esc_html_e( "Default Label" ); ?></th>
            <?php foreach ( $available_translations as $translation) {
                echo "<th>". esc_html( $translation["native_name"] ) . "</th>";
            }?>
            </tr></thead><tr>

            <?php
            $fields = $this->get_post_fields( $post_type );
            dt_write_log( $fields );
            $custom_field_options = dt_get_option( "dt_field_customizations" );
            dt_write_log( $custom_field_options["contacts"]["milestones"]["default"]["test_milestone"] );

            $custom_field_options["contacts"]["milestones"]["default"]["test_milestone"]["label_fa_IR"] = "Test Milestone Farsi";

            dt_write_log( $custom_field_options["contacts"]["milestones"]["default"]["test_milestone"] );
            update_option( "dt_field_customizations", $custom_field_options );
            if ( $fields ){
                foreach ( $fields as $field_key => $field_value ){
                    // dt_write_log( $field_key );
                    if ( isset( $field_value["customizable"] ) ){
                        foreach ( $field_value["default"] as $term ){
                            echo "<td>" . esc_html( $term["label"] ) . "</td>";
                            foreach ( $available_translations as $translation) {
                                $translation_label_key = "label_" . $translation['language'];
                                $translation_label_value = $term["label_". $translation['language']] ?? '';
                                ?>
                                <td><input class="test" type="text" name="<?php echo esc_attr( $translation_label_key )?>" value="<?php echo esc_attr( $translation_label_value ) ?>"></td>
                                <?php
                            }?>
                            </tr> <!-- end term row -->
                            <?php
                        }
                    }
                }
            }
        } ?>
        </tr>
        <table>
        <button type="submit" style="float:right;" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>
        </form>
        <?php
    }

    private function process_add_field( $post_submission ){
        if ( isset( $post_submission["new_field_name"], $post_submission["new_field_type"], $post_submission["post_type"] ) ){
            $post_type = $post_submission["post_type"];
            $field_type = $post_submission["new_field_type"];
            $field_tile = $post_submission["new_field_tile"] ?? '';
            $field_key = dt_create_field_key( $post_submission["new_field_name"] );
            if ( !$field_key ){
                return false;
            }
            $post_fields = $this->get_post_fields( $post_type );
            if ( isset( $post_fields[ $field_key ] )){
                self::admin_notice( __( "Field already exists", 'disciple_tools' ), "error" );
                return false;
            }
            $new_field = [];
            if ( $field_type === "key_select" ){
                $new_field = [
                    'name' => $post_submission["new_field_name"],
                    'default' => [],
                    'type' => 'key_select',
                    'tile' => $field_tile,
                    'customizable' => 'all'
                ];
            } elseif ( $field_type === "multi_select" ){
                $new_field = [
                    'name' => $post_submission["new_field_name"],
                    'default' => [],
                    'type' => 'multi_select',
                    'tile' => $field_tile,
                    'customizable' => 'all'
                ];
            } elseif ( $field_type === "date" ){
                $new_field = [
                    'name'        => $post_submission["new_field_name"],
                    'type'        => 'date',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all'
                ];
            } elseif ( $field_type === "text" ){
                $new_field = [
                    'name'        => $post_submission["new_field_name"],
                    'type'        => 'text',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all'
                ];
            }

            $custom_field_options = dt_get_option( "dt_field_customizations" );
            $custom_field_options[$post_type][$field_key] = $new_field;
            update_option( "dt_field_customizations", $custom_field_options );
            wp_cache_delete( $post_type . "_field_settings" );
            self::admin_notice( __( "Field added successfully", 'disciple_tools' ), "success" );
            return $field_key;
        }
        return false;
    }

    /**
     * Display admin notice
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ) ?></p>
        </div>
        <?php
    }
}
Disciple_Tools_Tab_Custom_Translations::instance();
