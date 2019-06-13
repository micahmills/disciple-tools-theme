<?php
declare(strict_types=1);

$url = dt_get_url_path();
$dt_post_type = explode( "/", $url )[0];

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_die( esc_html( "Permission denied" ), "Permission denied", 403 );
}

get_header();

?>

    <div id="content">
        <div id="inner-content" class="" style="display: flex; justify-content: space-around">
            <div style="background-color: white; flex-basis: 33%; padding: 10px">
                <div id="chartdiv"></div>
            </div>
            <div style="background-color: white; flex-basis: 33%; padding: 10px">Needs Accepting</div>
            <div style="background-color: white; flex-basis: 33%; padding: 10px">Test</div>
        </div>
    </div>

    <script>
        jQuery(function($) {

        });
    </script>


<?php
get_footer();
