<?php
/**
* Plugin Name: SaleEvent Coupons
* Plugin URI: http://beecommerce.pl/do-pobrania/
* Description: SaleEvent plugin creates coupons that have all important meta data for SaleEvent by Schema.org, such as: location, title, start date, end date, description, link.
* Plugin displays the coupons in three groups: Active coupons, Future coupons and Past coupons. For active ones, plugin displays link and days remaining, for the future ones - days to start.
* Apart from that, the plugin displays also an archive with pagination for the past coupons if there is more that 8 of them.
* Version: 1.1
* Author: Beecommerce.pl team
* Author URI: http://beecommerce.pl/
* License: GPL2

* SaleEvent plugin is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* any later version.
*SaleEvent plugin is distributed in the hope that it will be useful,
*but WITHOUT ANY WARRANTY; without even the implied warranty of
*MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*GNU General Public License for more details.
*You should have received a copy of the GNU General Public License
*along with SaleEvent plugin. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
*/


//detecting paths and url inside plugin folder
 if ( ! defined( 'ABSPATH' ) ) exit;
define('SALE_EVENT_VERSION', '1.1');
define('SALE_EVENT_DIR', plugin_dir_path(__FILE__));
define('SALE_EVENT_URL', plugin_dir_url(__FILE__));


//activate plugin and create custom table
function sale_event_activation() {
	//actions to perform once on plugin activation

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta_sale_events';

  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
    $sql = "CREATE TABLE " . $table_name . " (
      post_id INTEGER NOT NULL AUTO_INCREMENT,
      title VARCHAR(55),
      start_date DATE,
      end_date DATE,
      link VARCHAR(55),
      text VARCHAR(55),
      PRIMARY KEY (post_id))";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
  //register uninstaller
   register_uninstall_hook(__FILE__, 'sale_event_uninstall');
}

//deactivate plugin
function sale_event_deactivation() {
	// actions to perform once on plugin deactivation go here
}

//uninstall plugin
function sale_event_uninstall(){
  //actions to perform once on plugin uninstall go here
}

//register SaleEvent coupons post type
function sale_event_register_post_types() {
    //  Set up the arguments for the post type.
    $args = array(
        'description'         => 'Sale Events post type description',
        'public'              => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'show_in_nav_menus'   => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-tickets',
        'can_export'          => true,
        'delete_with_user'    => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'has_archive'         => true,
        'query_var'           => true,
        'supports' => array('title','thumbnail'),
        'labels' => array(
            'name'               =>  'Sale Events',
            'singular_name'      =>  'Sale Event',
            'menu_name'          =>  'Sale Events',
            'name_admin_bar'     =>  'Sale Events',
            'add_new'            =>  'Add New',
            'add_new_item'       =>  'Add New Sale Event',
            'edit_item'          =>  'Edit Sale Event',
            'new_item'           =>  'New Sale Event',
            'view_item'          =>  'View Sale Event',
            'search_items'       =>  'Search Sale Events',
            'not_found'          =>  'No Sale Events found',
            'not_found_in_trash' =>  'No Sale Events found in trash',
            'all_items'          =>  'All Sale Events',
            'archive_title'      =>  'Sale Events',
        )
    );

    //  Register the post type.
    register_post_type( 'sale-event', $args );
    register_taxonomy("Sale Events", array("sale-events"), array("hierarchical" => true, "label" => "Sale Events", "singular_label" => "Sale Event", "rewrite" => true));
}
add_action( 'init', 'sale_event_register_post_types' );


//add custom meta boxes for SaleEvent coupon details
function add_coupon_details(){
  global $post;

  //autofill data for already created coupons
  $current_start_date = get_post_meta( $post->ID, 'start_date', true );
  $current_end_date = get_post_meta( $post->ID, 'end_date', true );
  $current_link = get_post_meta( $post->ID, 'link', true );
  $current_text = get_post_meta( $post->ID, 'text', true );

  wp_nonce_field( basename( __FILE__ ), 'wpse_our_nonce' );
  // settings_fields( 'sale-event-settings' );
  // do_settings_sections( 'sale-event-settings' ); ?>

  <?php settings_errors(); ?>

  <table class="form-table">
    <tr valign="top">
    <th scope="row">Coupon start date:</th>
    <td><input type="date" name="sale-event-start-date" value="<?php echo esc_html($current_start_date); ?>"/></td>
    </tr>
    <tr valign="top">
    <th scope="row">Coupon end date:</th>
    <td><input type="date" name="sale-event-end-date" value="<?php echo esc_html($current_end_date); ?>"/></td>
    </tr>
    <tr valign="top">
    <th scope="row">Coupon link:</th>
    <td><input type="url" name="sale-event-link" value="<?php echo esc_url($current_link); ?>"/></td>
    </tr>
    <tr valign="top">
    <th scope="row">Coupon text:</th>
    <td><textarea rows="4" cols="50" name="sale-event-text"><?php echo esc_textarea($current_text); ?></textarea></td>
    </tr>
  </table>
  <?php
}

function sale_event_add_custom_meta_box(){
  add_meta_box("sale_event_start_date", "Add coupon details", "add_coupon_details", "sale-event");
}

add_action("add_meta_boxes", "sale_event_add_custom_meta_box");


// save SaleEvent coupons data
add_action( 'save_post', 'sale_event_box_save', 10, 2 );


function sale_event_admin_notice_error() {
  if(isset($_GET['msg'])) {
  ?>
  <div class="notice notice-error">
    <p>Please fill up all the fields correctly!</p>
  </div>
  <?php
  }
}

function sale_event_box_save( $post_id, $post ) {
  // check autosave
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
  return;

  // verify nonce
  if ( !wp_verify_nonce( $_POST['wpse_our_nonce'], basename( __FILE__ ) ) )
  return;

  // verify if user can edit post
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
    return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
    return;
  }

  //save coupon details
  if($post->post_type === 'sale-event') {
    $title = $post->post_title;
    $start_date = sanitize_text_field($_POST['sale-event-start-date']);
    $end_date = sanitize_text_field($_POST['sale-event-end-date']);
    $link = sanitize_text_field($_POST['sale-event-link']);
    $text = sanitize_text_field($_POST['sale-event-text']);

    $prevent_publish = false;

    if (empty($title) && !$prevent_publish) {
      $prevent_publish = true;
    } else {
      update_post_meta( $post_id, 'title', $title );
    }

    if ((empty($start_date) || !(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$start_date))) && !$prevent_publish) {
      $prevent_publish = true;
    } else {
      update_post_meta( $post_id, 'start_date', $start_date );
    }

    if($end_date === ''){
      add_post_meta($post_id, 'end_date', '0000-00-00', true);
      if ( ! add_post_meta( $post_id, 'end_date', '0000-00-00', true ) ) {
        update_post_meta( $post_id, 'end_date', '0000-00-00' );
      }
    } else if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$end_date) && $end_date > $start_date){
      update_post_meta( $post_id, 'end_date', $end_date );
    } else if (!$prevent_publish){
      $prevent_publish = true;
    }

    if (filter_var($link, FILTER_VALIDATE_URL) == false && !$prevent_publish) {
      $prevent_publish = true;
    } else {
      update_post_meta( $post_id, 'link', $link );
    }

    update_post_meta( $post_id, 'text', $text );

    if ($prevent_publish) {
        remove_action('save_post', 'sale_event_box_save');

        wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));

        add_action('save_post', 'sale_event_box_save', 10, 2);

    }


  }
}


add_filter('redirect_post_location','sale_event_redirect_location',10,2);
function sale_event_redirect_location($location,$post_id){
    if (isset($_POST['publish']) || isset($_POST['save'])){
        $status = get_post_status( $post_id );
        if($status=='draft'){
            $location = add_query_arg('msg', 10, $location);
        }
      return $location;
    }
}


add_filter( 'post_updated_messages', 'post_published_with_error' );

function post_published_with_error( $messages ) {
  if($_GET['msg'] == 10){
    add_action( 'admin_notices', 'sale_event_admin_notice_error' );
    unset($messages['post'][6]);
    unset($messages['post'][10]);
  }
  return $messages;
}
// Insert coupon details into custom database table
add_action( 'wp_insert_post', 'sale_event_meta_insert_post', 10, 2 );
function sale_event_meta_insert_post( $post_id, $post ) {
    global $wpdb;
    wp_create_nonce( basename(__FILE__) ) . '" />';

    $title = $post->post_title;
    $start_date = sanitize_text_field($_POST['sale-event-start-date']);
    $end_date = sanitize_text_field($_POST['sale-event-end-date']);
    $link = sanitize_text_field($_POST['sale-event-link']);
    $text = sanitize_text_field($_POST['sale-event-text']);

    if (    get_post_type( $post ) == 'sale-event' &&
        $post->post_status != 'trash' &&
        $wpdb->get_var( $wpdb->prepare("
            SELECT  post_id
            FROM    " . $wpdb->prefix . "postmeta_sale_events
            WHERE   post_id = %d
        ", $post_id ) ) == null
    ) {
        $wpdb->insert( $wpdb->prefix . "postmeta_sale_events", array( 'post_id' => $post_id, 'title' => $title, 'start_date' => $start_date, 'end_date' => $end_date, 'link' => $link, 'text' => $text, ) );
    }
}

// Delete custom meta records when a post is deleted
add_action( 'delete_post', 'sale_event_delete_post' );
function sale_event_delete_post( $post_id ) {
    if ( get_post_type( $post_id ) == 'sale_event' ) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare( "
            DELETE FROM " . $wpdb->prefix . "postmeta_sale_events
            WHERE       post_id = %d
        ", $post_id ) );
    }
}

// Re-route saving custom post meta for posts
add_filter( 'update_post_metadata', 'sale_event_custom_meta_update', 0, 4 );
add_filter( 'add_post_metadata', 'sale_event_custom_meta_update', 0, 4 );
function sale_event_custom_meta_update( $check, $post_id, $meta_key, $meta_value ) {
    if ( get_post_type( $post_id) == 'sale-event' && in_array( $meta_key, array( 'title', 'start_date', 'end_date', 'link', 'text' ) ) ) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . "postmeta_sale_events",
            array( $meta_key => maybe_serialize( $meta_value ) ),
            array( 'post_id' => $post_id)
        );
    } else {
        return $check;
    }
}

// Re-route deleting custom post meta for posts
add_filter( 'delete_post_metadata', 'sale_event_custom_meta_delete', 0, 3 );
function sale_event_custom_meta_delete( $check, $post_id, $meta_key ) {
    if ( get_post_type( $post_id ) == 'sale-event' && in_array( $meta_key, array( 'title', 'start_date', 'end_date', 'link', 'text' ) ) ) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . "postmeta_sale_events",
            array( $meta_key => null ),
            array( 'post_id' => $post_id )
        );
    } else {
        return $check;
    }
}

// Re-route getting custom meta for posts
add_filter( 'get_post_metadata', 'sale_event_custom_meta_get', 0, 3 );
function sale_event_custom_meta_get( $check, $post_id, $meta_key ) {
    global $wpdb;

    if ( get_post_type( $post_id) == 'sale-event' && in_array( $meta_key, array( 'title', 'start_date', 'end_date', 'link', 'text' ) ) ) {
        $result = $wpdb->get_var( $wpdb->prepare("
            SELECT  $meta_key
            FROM    " . $wpdb->prefix . "postmeta_sale_events
            WHERE   post_id = %d
        ", $post_id) );
        return maybe_unserialize( $result );
    } else {
        return $check;
    }
}

// if( !function_exists("update_sale_event") ) {
// function update_sale_event() {
//   register_setting( 'sale-event-settings', 'sale_event' );
// }
// }


//activation and deactivation plug
register_activation_hook(__FILE__, 'sale_event_activation');
register_deactivation_hook(__FILE__, 'sale_event_deactivation');

//add new columns on admin page
add_filter( 'manage_edit-sale-event_columns', 'sale_event_columns' );

function sale_event_columns($columns) {
	$columns['sale-event_start_date'] = 'Start date';
	$columns['sale-event_end_date'] = 'End date';
	$columns['sale-event_link'] = 'Link';
	return $columns;
}

add_action( 'manage_posts_custom_column', 'populate_sale_event_columns' );
function populate_sale_event_columns($column) {
    if('sale-event_start_date' == $column) {
		$start_date = esc_html( get_post_meta( get_the_ID(), 'start_date', true ) );
		echo $start_date;
	} else if('sale-event_end_date' == $column) {
		$end_date = esc_html( get_post_meta( get_the_ID(), 'end_date', true ) );
		echo $end_date;
	} else if('sale-event_link' == $column) {
		$link = esc_html( get_post_meta( get_the_ID(), 'link', true ) );
		echo $link;
	}
}

//add shortcode
  function sale_event_shortcode(){
    ob_start();
    include(SALE_EVENT_DIR.'archive-sale-event.php');
    $output = ob_get_clean();
    return $output;
  }
  add_shortcode("sale_event", "sale_event_shortcode");

  function sale_event_archive_shortcode(){
    ob_start();
    include(SALE_EVENT_DIR.'coupons-archive.php');
    $output = ob_get_clean();
    return $output;
  }
  add_shortcode("sale_event_archive", "sale_event_archive_shortcode");


  //add templates
  function include_sale_event_templates_function( $template_path ) {
    if ( get_post_type() == 'sale-event' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-sale-event.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . '/single-sale-event.php';
            }
        }
        elseif ( is_archive() ) {
            if ( $theme_file = locate_template( array ( 'archive-sale-event.php' ) ) ) {
                $template_path = $theme_file;
            } else { $template_path = plugin_dir_path( __FILE__ ) . '/archive-sale-event.php';

            }
        }
    }
    return $template_path;
  }

  add_filter('template_include', 'include_sale_event_templates_function', 1);

  //add css $ javaScript files
  add_action( 'wp_enqueue_scripts', 'sale_event_stylesheet' );

    function sale_event_stylesheet() {
      // Respects SSL, Style.css is relative to the current file
      wp_register_style( 'sale-event-style', plugins_url('css/sale-event.css', __FILE__) );
      wp_enqueue_style( 'sale-event-style' );
    }

  add_action( 'wp_enqueue_scripts', 'sale_event_script' );

    function sale_event_script() {
       wp_register_script( 'sale-event', plugins_url('/js/sale-event.js', __FILE__), array( 'jquery' ));
  	   wp_enqueue_script( 'sale-event');
    }


    class SaleEventSettingsPage{
        /**
         * Holds the values to be used in the fields callbacks
         */
        private $options;

        /**
         * Start up
         */
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
        }

        /**
         * Add options page
         */
        public function add_plugin_page()
        {
            // This page will be under "Settings"
            add_submenu_page( 'edit.php?post_type=sale-event',
                'Settings Admin',
                'Settings',
                'manage_options',
                'my-setting-admin',
                array( $this, 'create_admin_page' )
            );
        }

        /**
         * Options page callback
         */
        public function create_admin_page()
        {
            // Set class property
            $this->options = get_option( 'sale_event_option_name' );
            ?>
            <div class="wrap">
                <h1>Settings</h1>
                <form method="post" action="options.php">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'sale_event_option_group' );
                    do_settings_sections( 'my-setting-admin' );
                    submit_button();
                ?>
                </form>
            </div>
            <?php
        }

        /**
         * Register and add settings
         */
        public function page_init()
        {
            register_setting(
                'sale_event_option_group', // Option group
                'sale_event_option_name', // Option name
                array( $this, 'sanitize' ) // Sanitize
            );

            add_settings_section(
                'setting_section_id', // ID
                'Add information about Your company', // Title
                array( $this, 'print_section_info' ), // Callback
                'my-setting-admin' // Page
            );

            add_settings_field(
                'company_name', // ID
                'Name of the company', // Title
                array( $this, 'company_name_callback' ), // Callback
                'my-setting-admin', // Page
                'setting_section_id' // Section
            );

            add_settings_field(
                'company_phone',
                'Phone number',
                array( $this, 'company_phone_callback' ),
                'my-setting-admin',
                'setting_section_id'
            );

            add_settings_field(
                'company_address',
                'Address',
                array( $this, 'company_address_callback' ),
                'my-setting-admin',
                'setting_section_id'
            );

            add_settings_field(
                'archive_url',
                'URL of Your archive page for past coupons',
                array( $this, 'archive_url_callback' ),
                'my-setting-admin',
                'setting_section_id'
            );


        }

        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         */
        public function sanitize( $input )
        {
            $new_input = array();
            if( isset( $input['company_name'] ) )
                $new_input['company_name'] = sanitize_text_field( $input['company_name'] );

            if( isset( $input['company_phone'] ) )
                $new_input['company_phone'] = sanitize_text_field( $input['company_phone'] );

            if( isset( $input['company_address'] ) )
                $new_input['company_address'] = sanitize_text_field( $input['company_address'] );

            if( isset( $input['archive_url'] ) )
                $new_input['archive_url'] = sanitize_text_field( $input['archive_url'] );

            return $new_input;
        }

        /**
         * Print the Section text
         */
        public function print_section_info()
        {
            print 'Enter your settings below:';
        }

        /**
         * Get the settings option array and print one of its values
         */
        public function company_name_callback()
        {
            printf(
                '<input type="text" id="company_name" name="sale_event_option_name[company_name]" value="%s" />',
                isset( $this->options['company_name'] ) ? esc_attr( $this->options['company_name']) : ''
            );
        }

        /**
         * Get the settings option array and print one of its values
         */
        public function company_phone_callback()
        {
            printf(
                '<input type="text" id="company_phone" name="sale_event_option_name[company_phone]" value="%s" />',
                isset( $this->options['company_phone'] ) ? esc_attr( $this->options['company_phone']) : ''
            );
        }

        public function company_address_callback()
        {
            printf(
                '<input type="text" id="company_address" name="sale_event_option_name[company_address]" value="%s" />',
                isset( $this->options['company_address'] ) ? esc_attr( $this->options['company_address']) : ''
            );
        }

        public function archive_url_callback()
        {
            printf(
                '<input type="text" id="archive_url" name="sale_event_option_name[archive_url]" value="%s" />',
                isset( $this->options['archive_url'] ) ? esc_attr( $this->options['archive_url']) : ''
            );
        }
    }

    if( is_admin() )
        $my_settings_page = new SaleEventSettingsPage();

  // });
?>
