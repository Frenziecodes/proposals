<?php
/**
 * Template for displaying single proposal
 *
 * @package Business_Proposals
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <?php while ( have_posts() ) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'wpbp-proposal-single' ); ?>>
                
                <?php
                // Get the plugin instance and render the proposal
                $wpbp_instance = WP_Business_Proposals::get_instance();
                $proposal_data = $wpbp_instance->get_proposal_data( get_the_ID() );
                echo $wpbp_instance->render_proposal_template( $proposal_data );
                ?>
                
            </article>
            
        <?php endwhile; ?>
        
    </main>
</div>

<?php
get_sidebar();
get_footer();
