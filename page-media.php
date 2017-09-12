<?php get_header(); ?>

    <div id="content">
        <!-- Breadcrumb Navigation-->
        <nav aria-label="You are here:" role="navigation" class="second-bar">
            <ul class="breadcrumbs">
                <li><a href="/">Dashboard</a></li>
                <li>
                    <span class="show-for-sr">Current: </span> MEDIA
                </li>
            </ul>
        </nav>

        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">

                <div class="row small-up-2 medium-up-3 large-up-4">

                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <?php get_template_part( 'parts/loop', 'media' ); ?>

                    <?php endwhile; ?>
                    <?php endif; ?>

                </div>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
