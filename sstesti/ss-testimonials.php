<?php
/*
Plugin Name: Rendi's Simple Testimonials
Description: Super simple testimonial form for SoftwareSeni recruitment test
Version: 1.0
Author: Rendi Dwi Pristianto
*/

//Prevent direct acces to this file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check if `SS_Helper` class is already exist, if not then create one
if ( ! class_exists( 'SS_Helper' ) ) {
	class SS_Helper {
		public $is_error = true;
		public $message = '';
		public $items = array();

		function get_serialized_val( $objs, $key ) {
			$result = false;
			$temres = array();
			foreach ( $objs as $obj ) {
				if ( $obj['name'] == $key ) {
					$temres[] = $obj['value'];
				}
			}
			$countarr = count( $temres );
			if ( $countarr > 0 ) {
				$result = count( $temres ) > 1 ? $temres : $temres[0];
			}

			return $result;
		}
	}
}

//Check if `SS_Testimonials` class is already exist, if not then create one
if ( ! class_exists( 'SS_Testimonials' ) ) {
	class SS_Testimonials {

		function __construct() {

			$this->loadfiles();
			//Add shortocde to display add form
			add_shortcode( 'ss_testimonial', array( $this, 'ss_shortcode_callback' ) );
		}

		//require other files
		function loadfiles() {
			require_once plugin_dir_path( __FILE__ ) . 'class.ssIO.php';
			require_once plugin_dir_path( __FILE__ ) . 'hooks.php';
			require_once plugin_dir_path( __FILE__ ) . 'ajax-handler.php';
		}

		//Callback for `ss_testimonial` shortcode
		function ss_shortcode_callback() {
			return $this->display_form();
		}

		//function to insert a new testimonial
		function insert( $name, $email, $phone, $content ) {
			$ssIO = new SS_IO();

			return $ssIO->insert( $name, $email, $phone, $content );
		}

//		//function to display random testimonial
		function get_random() {
			$ssIO = new SS_IO();

			return $ssIO->get_random();
		}

		//function to delete testimonial
		function delete( $id ) {
			$ssIO = new SS_IO();

			return $ssIO->delete( $id );
		}

		//function to display testimonials
		function display() {
			$ssIO = new SS_IO();

			return $ssIO->display();
		}

		//function to display insert form
		function display_form() {
			$htmlresult = '<div class="parentform">';
			$htmlresult .= '<div class="ntf"></div>';
			$htmlresult .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post">';
			// echo '<input type="hidden" name="action" value="sstestimonials"/>';
			$htmlresult .= wp_nonce_field( 'ss_val_nonce', 'ss_form_nonce', true, false );
			$htmlresult .= '<p>Name<br/><input type="text" name="ss_name" pattern="[a-zA-Z0-9 ]+" value="" required/></p>';
			$htmlresult .= '<p>Email<br/>';
			$htmlresult .= '<input type="email" name="ss_email" value="" required/></p>';
			$htmlresult .= '<p>Phone Number<br/>';
			$htmlresult .= '<input type="text" name="ss_phone" pattern="[0-9]+" value="" required/></p>';
			$htmlresult .= '<p>Testimonial<br/><textarea rows="10" name="ss_testi" required></textarea></p>';
			$htmlresult .= '<p><button type="button" class="btnsend" name="ss_submit">Submit</button></p>';
			$htmlresult .= '</form>';
			$htmlresult .= '</div>';

			return $htmlresult;
		}
	}

}

//Prepare custom table and dummy record
register_activation_hook( __FILE__, 'ss_first_init' );
function ss_first_init() {

	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'testimonials';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		phone tinytext NOT NULL,
		email tinytext NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	$wpdb->insert(
		$table_name,
		array(
			'name'  => 'Rendy',
			'time'  => current_time( 'mysql' ),
			'text'  => 'Hi there, this is just a dummy text, thank you',
			'phone' => '082219186349',
			'email' => 'rendy.de.p@gmail.com',
		)
	);

}

$ssTestimonials = new SS_Testimonials();