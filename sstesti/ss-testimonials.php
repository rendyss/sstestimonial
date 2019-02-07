<?php
/*
Plugin Name: Rendi's Simple Testimonials
Description: Super simple testimonial form for SoftwareSeni recruitment test
Version: 1.0
Author: Rendi Dwi Pristianto
*/

//Prevent direct acces to this file
if(!defined('ABSPATH')){
	exit;
}

//Check if `SS_Helper` class is already exist, if not then create one
if(!class_exists('SS_Helper')){
	class SS_Helper
	{
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
if(!class_exists('SS_Testimonials')){
	class SS_Testimonials
	{
		public $tId;
		public $tName;
		public $tEmail;
		public $tPhone;
		public $tContent;
		public $tTime;
	
		function __construct(){		
			
			//Require other files
			require_once "ajax-handler.php";
			require_once "hooks.php";

			//Add shortocde to display add form
			add_shortcode( 'ss_testimonial', array($this, 'ss_shortcode_callback' ));

		}

		//Callback for `ss_testimonial` shortcode
		function ss_shortcode_callback() {
			$res = $this->display_form();
		}

		//function to insert a new testimonial
		function insert($name, $email, $phone, $content){
			$result = new SS_Helper();
			global $wpdb;
			$insert = $wpdb->insert( $wpdb->prefix . 'testimonials', array(
				'name'  => $name,
				'time'  => current_time( 'mysql' ),
				'email' => $email,
				'phone' => $phone,
				'text'  => $content
			) );
			if(!$insert){
				$result->message = "Failed to insert testimonial";
			}else{
				$result->is_error =false;
			}
			return $result;
		}

		//function to display random testimonial
		function get_random(){
			global $wpdb;
			$random = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'testimonials ORDER BY RAND()', ARRAY_A );
			if($random){
				$this->tId = $random['id'];
				$this->tName = $random['name'];
				$this->tEmail = $random['email'];
				$this->tPhone = $random['phone'];
				$this->tContent = $random['text'];
				$this->tTime = $random['time'];
			}
			return $this;
		}

		//function to delete testimonial
		function delete($id){
			global $wpdb;
			$result = new SS_Helper();
			if($id){
				$delete = $wpdb->delete( $wpdb->prefix . 'testimonials', array( 'id' => $id ) );		
				if($delete){
					$result->is_error = false;
				}
			}else{
				$ressult->message = "Please provide valid id";
			}
			return $result;
		}

		//function to display testimonials
		function display(){
			$result = new SS_Helper();
			global $wpdb;
			$allTestimonials = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'testimonials', ARRAY_A );
			if($allTestimonials){
				$result->items = $allTestimonials;
				$result->is_error = false;
			}
			return $result;
		}

		//function to display insert form
		function display_form(){
			echo '<div class="parentform">';
			echo '<div class="ntf"></div>';
				echo '<form action="' . esc_url( admin_url( 'admin-post.php') ) . '" method="post">';
				// echo '<input type="hidden" name="action" value="sstestimonials"/>';
				wp_nonce_field('ss_val_nonce', 'ss_form_nonce');
				echo '<p>Name<br/><input type="text" name="ss_name" pattern="[a-zA-Z0-9 ]+" value="" required/></p>';
				echo '<p>Email<br/>';
				echo '<input type="email" name="ss_email" value="" required/></p>';
				echo '<p>Phone Number<br/>';
				echo '<input type="text" name="ss_phone" pattern="[0-9]+" value="" required/></p>';
				echo '<p>Testimonial<br/><textarea rows="10" name="ss_testi" required></textarea></p>';
				echo '<p><button type="button" class="btnsend" name="ss_submit">Submit</button></p>';
				echo '</form>';
				echo '</div>';
		}
	}
	
}

$ssTestimonials = new SS_Testimonials();