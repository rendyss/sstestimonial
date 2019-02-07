<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2/7/2019
 * Time: 2:47 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SS_IO' ) ) {
	class SS_IO {
		public $tId;
		public $tName;
		public $tEmail;
		public $tPhone;
		public $tContent;
		public $tTime;

		//function to insert a new testimonial
		function insert( $name, $email, $phone, $content ) {
			$result = new SS_Helper();
			global $wpdb;
			$insert = $wpdb->insert( $wpdb->prefix . 'testimonials', array(
				'name'  => $name,
				'time'  => current_time( 'mysql' ),
				'email' => $email,
				'phone' => $phone,
				'text'  => $content
			) );
			if ( ! $insert ) {
				$result->message = "Failed to submit testimonial";
			} else {
				$result->message  = "Testimonial submitted successfully";
				$result->is_error = false;
			}

			return $result;
		}

		//function to display random testimonial
		function get_random() {
			global $wpdb;
			$random = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'testimonials ORDER BY RAND()', ARRAY_A );
			if ( $random ) {
				$this->tId      = $random['id'];
				$this->tName    = $random['name'];
				$this->tEmail   = $random['email'];
				$this->tPhone   = $random['phone'];
				$this->tContent = $random['text'];
				$this->tTime    = $random['time'];
			}

			return $this;
		}

		//function to delete testimonial
		function delete( $id ) {
			global $wpdb;
			$result = new SS_Helper();
			if ( $id ) {
				$delete = $wpdb->delete( $wpdb->prefix . 'testimonials', array( 'id' => $id ) );
				if ( $delete ) {
					$result->is_error = false;
				}
			} else {
				$result->message = "Please provide valid id";
			}

			return $result;
		}

		//function to display testimonials
		function display() {
			$result = new SS_Helper();
			global $wpdb;
			$allTestimonials = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'testimonials', ARRAY_A );
			if ( $allTestimonials ) {
				$result->items    = $allTestimonials;
				$result->is_error = false;
			}

			return $result;
		}
	}
}