<?php

// Post meta single output for all fields
function sandbox_get_post_meta($post_id=0){
  $numeric_fields = array('_alpha_file_count', '_favorites', '_wpcr_rating', '_thumbnail_id');
  $serialized_fields = array('_more-link', '_alpha_file_options', '_section-linked', '_conditions-table', '_links-internal', '_links-external');
  $encode_fields = array('_team-phone', '_team-email');
  $post_id = absint($post_id);
  if ( !$post_id ) {
    $post_id = get_the_ID();
  }
  $post_custom = get_post_custom($post_id);
  $post_meta_keys = get_post_custom_keys($post_id);

  $post_meta = array();
  
  if ( $post_custom !== false && !empty($post_custom) ) {
    foreach ( $post_meta_keys as $post_meta_key ) {
      if ( !empty($post_custom[$post_meta_key][0]) ) {
        $post_meta[$post_meta_key] = $post_custom[$post_meta_key][0];
        // exeptions
        // numeric field type
        if ( in_array($post_meta_key, $numeric_fields) ) {
          $post_meta[$post_meta_key] = intval($post_custom[$post_meta_key][0]);
        }
        // serialized field type
        if ( in_array($post_meta_key, $serialized_fields) ) {
          $post_meta[$post_meta_key] = unserialize($post_custom[$post_meta_key][0]);
        }
        // fields to output encoded
        if ( in_array($post_meta_key, $encode_fields) ) {
          if (function_exists('eae_encode_str') ) {
            $post_meta[$post_meta_key] = eae_encode_str($post_custom[$post_meta_key][0]);
            $post_meta[$post_meta_key . '_unencoded'] = $post_custom[$post_meta_key][0];
          }
        }
      } else {
        $post_meta[$post_meta_key] = '';
        // exeptions
        // numeric field type
        if ( in_array($post_meta_key, $numeric_fields) ) {
          $post_meta[$post_meta_key] = 0;
        }
        // serialized field type
        if ( in_array($post_meta_key, $serialized_fields) ) {
          $post_meta[$post_meta_key] = array();
        }
      }
    }
  }
  return $post_meta;
}
?>
