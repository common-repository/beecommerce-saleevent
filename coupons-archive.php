
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define('DONOTCACHEDB', true);

$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
$today = date( 'Y-m-d' );
$q = new WP_Query(
  array (
    'post_type' => 'sale-event',
    'meta_query' => array(
        array(
            'key' => 'end_date',
            'value' => $today,
            'compare' => '<=',
            'type' => 'DATE'
          )
      ),
    'posts_per_page' => 5,
    'paged' => $paged  ) );

$list = ' ';  ?>


  <section id="coupons">
    <div id="content" role="main">
    <?php if ( $q->have_posts() ) : ?>
        <header class="page-header">
          <h1 class="page-title">Archive</h1>
        </header>

        <?php
        $sale_event_option = esc_html(get_option('sale_event_option_name'));
        $company_name = esc_html($sale_event_option['company_name']);
        $company_address = esc_html($sale_event_option['company_address']);
        $company_telephone = esc_html($sale_event_option['company_phone']);
        ?>

      <div class="past-coupons-archive">

          <?php while ( $q->have_posts() ) : $q->the_post();

              $start = esc_html(get_post_meta( get_the_ID(), 'start_date', true ));
              $now = time();
              $end = esc_html(get_post_meta( get_the_ID(), 'end_date', true ));
              $days_between = ceil((strtotime($end) - $now) / 86400);
              $days_to_start = ceil((strtotime($start) - $now) / 86400);

               ?>
              <div itemscope itemtype="http://schema.org/SaleEvent" class="coupon-content expired">
                <?php echo '<meta itemprop="startDate" content="'. $start .'" />' ?>
                <?php echo '<meta itemprop="endDate" content="'. $end .'" />' ?>
                <?php echo '<meta itemprop="url" content="'. esc_html( get_post_meta( get_the_ID(), 'link', true ) ) .'" />' ?>
                <div itemprop='location' itemscope itemtype="http://schema.org/Place">
                  <?php echo '<meta itemprop="name" content="'. $company_name .'" />' ?>
                  <?php echo '<meta itemprop="address" content="'. $company_address .'" />' ?>
                  <?php echo '<meta itemprop="telephone" content="'. $company_telephone .'" />' ?>
                </div>
                <div class="coupon-thumbnail">
                  <?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'coupon-image' ) ); ?>
                </div>
                <div class="coupon-details">
                  <span>DISCOUNT CODE</span><br>
                  <h5 itemprop='name' class="coupon-title"><?php the_title(); ?></h5>
                  <p itemprop='description' class="coupon-text"><?php echo esc_html( get_post_meta( get_the_ID(), 'text', true ) ); ?></p>
                </div>
                <div class="coupon-dates">
                  <span>This coupon has expired.</span>
                </div>
              </div>

            <?php

          endwhile; ?>
      </div>

      <!-- options -->
      <div class="col-md-12 options border-bottom">

          <!-- pagination -->
          <ul class="pagination">
              <li class="next-coupon"><?php echo get_next_posts_link( 'Next', $q->max_num_pages ); ?></li>
              <li class="prev-coupon"><?php echo get_previous_posts_link( 'Previous' ); ?></li>
          </ul>

      </div>

      <?php wp_reset_postdata(); ?>

    </div>
  </section>
<br /><br />

<?php endif; ?>
