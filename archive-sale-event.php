
<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$today = date( 'Y-m-d' );
$query_active = new WP_Query(
  array (
    'post_type' => 'sale-event',
      'meta_query' => array(
  		'relation' => 'OR',
  		 array(
  			'key'     => 'end_date',
  			'value'   => '0000-00-00',
  			'compare' => '=',
        'type' => 'DATE'
  		),
                  array(
                          'relation' => 'AND',
                          array(
                                  'key' => 'end_date',
                                  'value' => $today,
                                  'compare' => '>=',
                                  'type' => 'DATE'
                          ),
                          array(
                                  'key' => 'start_date',
                                  'value' => $today,
                                  'compare' => '<=',
                                  'type' => 'DATE'
                          ),
  		),
  	),
       ) );
?>

  <section id="coupons">
    <div id="content" role="main">

        <header class="page-header">
          <h1 class="page-title">Discount codes</h1>
        </header>

        <?php
        $sale_event_option = esc_html(get_option('sale_event_option_name'));
        $company_name = esc_html($sale_event_option['company_name']);
        $company_address = esc_html($sale_event_option['company_address']);
        $company_telephone = esc_html($sale_event_option['company_phone']);
        ?>

        <div class="active-coupons">

        <h3 class="coupon-header">ACTIVE COUPONS</h3>
            <!-- Start the Loop -->
            <?php
           if ( $query_active->have_posts() ) :
            while ( $query_active->have_posts() ) : $query_active->the_post();
              $start = esc_html(get_post_meta( get_the_ID(), 'start_date', true ));
              $now = time();
              $end = esc_html(get_post_meta( get_the_ID(), 'end_date', true ));
              $days_between = ceil((strtotime($end) - $now) / 86400); ?>

                  <div itemscope itemtype="http://schema.org/SaleEvent" class="coupon-content">
                      <?php echo '<meta itemprop="startDate" content="'. $start .'" />' ?>
                      <?php echo '<meta itemprop="endDate" content="'. $end .'" />' ?>
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
                        <a itemprop='url' href="<?php echo esc_html( get_post_meta( get_the_ID(), 'link', true ) ); ?>"><div class="button-coupon">SEE MORE</div></a><br>
                        <?php if($end !== '0000-00-00'){ ?>
                        <span>The code will expire in <?php echo $days_between ?> days!</span>
                        <?php }else { ?>
                        <span>The code is valid until further notice. </span>
                        <?php } ?>
                      </div>
                  </div>
          <?php
           endwhile;
         endif; ?>

      </div>
<?php
      $query_future = new WP_Query(
        array (
          'post_type' => 'sale-event',
          'meta_query' => array(
              array(
                  'key' => 'start_date',
                  'value' => $today,
                  'compare' => '>',
                  'type' => 'DATE'
                )
            ) ) );
            ?>
      <div class="future-coupons">
          <h3 class="coupon-header">FUTURE COUPONS</h3>
          <?php
          if ( $query_future->have_posts() ) :
            while ( $query_future->have_posts() ) : $query_future->the_post();

              $start = get_post_meta( get_the_ID(), 'start_date', true );
              $now = time();
              $end = get_post_meta( get_the_ID(), 'end_date', true );
              $days_to_start = ceil((strtotime($start) - $now) / 86400); ?>

                <div itemscope itemtype="http://schema.org/SaleEvent" class="coupon-content notStarted">
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
                     <span><?php echo $days_to_start ?> days till start!</span>
                   </div>
                </div>
                <?php
             endwhile;
           endif; ?>
      </div>

      <?php $query_past = new WP_Query(
        array (
          'post_type' => 'sale-event',
          'meta_query' => array(
              array(
                  'key' => 'end_date',
                  'value' => $today,
                  'compare' => '<=',
                  'type' => 'DATE'
                ),
                array(
                 'key'     => 'end_date',
                 'value'   => '0000-00-00',
                 'compare' => '!=',
                 'type' => 'DATE'
               ),
          'posts_per_page' => 8
            ) ) );

      ?>

      <div class="past-coupons">
        <h3 class="coupon-header">EXPIRED COUPONS</h3>

          <?php $num_of_expired = 0 ?>
          <?php
          if ( $query_past->have_posts() ) :
            while ( $query_past->have_posts() ) : $query_past->the_post();

              $start = esc_html(get_post_meta( get_the_ID(), 'start_date', true ));
              $now = time();
              $end = esc_html(get_post_meta( get_the_ID(), 'end_date', true )); ?>

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
            $num_of_expired++;

            if($num_of_expired == 8){
              break;
            }
          endwhile;
        endif; ?>

          <?php if($num_of_expired == 8){
            $archive_url = esc_html($sale_event_option['archive_url']);
            echo '<a href="'.$archive_url.'" class="button medium primary coupons-archive">SEE THE ARCHIVE</a>';
          } ?>

      </div>

    </div>
  </section>
<br /><br />
