<?php
declare(strict_types=1);

$url = dt_get_url_path();
$dt_post_type = explode( "/", $url )[0];

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_die( esc_html( "Permission denied" ), "Permission denied", 403 );
}

get_header();

?>

    <div id="content" class="dashboard-page">
        <div id="inner-content">
            <div class="dash-cards">
                <div class="item" style="flex-basis: 33%;">
                    <div class="card" style="height: 100%">
                        <div style="display: flex; flex-direction: column; height: 100%">
                            <div style="text-align: center">
                                <span class="card-title">Active Contacts</span>
                            </div>
                            <div style="text-align: center; flex-grow: 1; margin-top: 20px">
                                <div style="background-color: #3f729b; border-radius: 100%; height: 150px; width:150px; margin-left: auto; margin-right:auto" >
                                    <span id="active_contacts" style="vertical-align: middle; font-size: 7rem; color: white"></span>
                                </div>
                            </div>
                            <div class="view-all" style="flex-shrink: 1">
                                <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "contacts/new" ?>">
                                    Add a contact
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="item" style="flex-basis: 33%;">
                    <div class="card">
                        <div style="display: flex; flex-direction: column; height: 100%">
                            <div>
                                <div style="background-color: rgba(217,220,0,0.47); " class="count-square">
                                    <span id="needs_accepting"></span>
                                </div>
                                <span class="card-title">
                                    Pending Contacts
                                </span>
                            </div>
                            <div id="needs_accepting_list"  style="flex-grow: 1"></div>
                            <div style="flex-shrink: 1" class="view-all">
                                <button class="button" id="view_needs_accepted_button">View All</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item" style="flex-basis: 33%;">
                    <div class="card">
                        <div style="display: flex; flex-direction: column; height: 100%">
                            <div>
                                <div style="background-color: rgba(236,17,17,0.2);" class="count-square">
                                    <span id="update_needed"></span>
                                </div>
                                <span class="card-title">
                                    Update Needed
                                </span>
                            </div>
                            <div id="update_needed_list" style="flex-grow: 1"></div>
                            <div class="view-all" style="flex-shrink: 1">
                                <button class="button" id="view_updated_needed_button">View All</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>





            <div class="dash-cards" id="benchmarks">
                <div class="item" style="flex-basis: 100%">
                <div class="card">
                    <div style="display: flex; flex-wrap: wrap">
                        <div style="flex-basis: 40%">
                            <h2 style="margin:50px">Personal Benchmarks</h2>
                            <ul style="list-style: none; margin-left: 50px">
                                <li>
                                    <div style="background-color: #C7E3FF; border-radius: 5px; height: 20px; width:20px; display: inline-block"></div>
                                    <span id="benchmarks_current" style="vertical-align: text-bottom"></span>
                                </li>
                                <li>
                                    <div style="background-color: #3f729b; border-radius: 5px; height: 20px; width:20px; display: inline-block"></div>
                                    <span id="benchmarks_previous" style="vertical-align: text-bottom"></span>
                                </li>
                            </ul>

                        </div>
                        <div style="flex-basis: 60%">
                            <div id="benchmark_chart" style="height: 300px"></div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

        </div>

        <div class="dash-cards">
            <div class="item" style="flex-basis: 50%">
                <div class="card">
                    <span class="card-title" style="text-align: center; margin-bottom: 30px">
                        Faith Milestone Totals
                    </span>
                    <div >
                        <div style="display: flex; flex-wrap: wrap" id="milestones">

                        </div>
                    </div>
                </div>
            </div>
            <div class="item" style="flex-basis: 50%">
                <div class="card">
                    <span class="card-title" style="text-align: center; margin-bottom: 15px">
                        Seeker Path Progress
                    </span>
                    <div id="seeker_path_chart" style="height:400px; width;200px"></div>

                </div>
            </div>

        </div>


    </div>

    <script>
        jQuery(function($) {

        });
    </script>


<?php
get_footer();
