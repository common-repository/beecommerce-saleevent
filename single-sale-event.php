<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header(); ?>
<div id="primary">
   <div id="content" role="main">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
          <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>
              <header class="entry-header">
                <!-- Display featured image in top-aligned floating div -->
                 <div style="float: top; margin: 10px">
                    <?php the_post_thumbnail( array( 100, 100 ) ); ?>
                 </div>
                 <!-- Display Title and Author Name -->
                 <strong>Coupon title: </strong><?php echo esc_html( get_post_meta( get_the_ID(), 'title', true ) ); ?><br />
                 <strong>Link: </strong>
                 <?php echo esc_html( get_post_meta( get_the_ID(), 'link', true ) ); ?>
                 <br />
              </header>
              <div class="entry-content">
                   <?php the_content(); ?>
              </div>
              <hr/>
         </article>
     <?php endwhile;  ?>
     <?php endif; ?>
   </div>
</div>
<?php wp_reset_query(); ?>
<?php get_footer(); ?>
