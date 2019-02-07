<?php

//Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SS_DBIO {

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
			$result->message = "Failed to insert testimonial";
		} else {
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

add_action( 'wp_ajax_sstestimonials', 'sstestimonials' );
add_action( 'wp_ajax_nopriv_sstestimonials', 'sstestimonials' );

function sstestimonials() {
	$result = new SS_Helper();
	//Assign posted data into variables
	$data     = $_POST['data'];
	$sData    = maybe_unserialize( $data );
	$vName    = $result->get_serialized_val( $sData, 'ss_name' );
	$vEmail   = $result->get_serialized_val( $sData, 'ss_email' );
	$vPhone   = $result->get_serialized_val( $sData, 'ss_phone' );
	$vContent = $result->get_serialized_val( $sData, 'ss_testi' );
	$vNonce   = $result->get_serialized_val( $sData, 'ss_form_nonce' );

	//Validate nonce
	if ( wp_verify_nonce( $vNonce, 'ss_val_nonce' ) ) {
		if ( $vName && $vEmail && $vPhone && $vContent ) {
			$ssDbIO = new SS_DBIO();
			$insert = $ssDbIO->insert( $vName, $vEmail, $vPhone, $vContent );
			$result = $insert;
			if ( ! $insert->is_error ) {
				$result->message = "Tetimonial successfully submitted";
			}
		} else {
			$result->message = "All fields are required";
		}
	} else {
		$result->message = "Failed to submit testimonial";
	}
	wp_send_json( $result );
}