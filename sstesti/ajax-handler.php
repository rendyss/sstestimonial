<?php 

//Prevent direct access to this file
if(!defined('ABSPATH')){
    exit;
}

add_action('wp_ajax_sstestimonials', 'sstestimonials');
add_action('wp_ajax_nopriv_sstestimonials', 'sstestimonials');

function sstestimonials(){
    $result = new SS_Helper();
    //Assign posted data into variables
    $data = $_POST['data'];
    $sData = maybe_unserialize( $data );
    $vName = $result->get_serialized_val( $sData,'ss_name' );
    $vEmail = $result->get_serialized_val( $sData,'ss_email');
    $vPhone = $result->get_serialized_val( $sData,'ss_phone' );
    $vContent = $result->get_serialized_val( $sData,'ss_testi' );
    $vNonce = $result->get_serialized_val($sData,'ss_form_nonce');

    //Validate nonce
    if(wp_verify_nonce($vNonce, 'ss_val_nonce')){
        if($vName && $vEmail && $vPhone && $vContent){
            $ssTestimonials = new SS_Testimonials();
            $insert = $ssTestimonials->insert($vName,$vEmail,$vPhone,$vContent);
            $result = $insert;
            if(!$insert->is_error){
                $result->message = "Tetimonial successfully submitted";
            }
        }else{
            $result->message = "All fields are required";
        }
    }else{
        $result->message = "Failed to submit testimonial";
    }
    wp_send_json( $result )
}
