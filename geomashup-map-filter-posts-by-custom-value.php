<?php
  // hide posts that has a value set at the custom field "_hide"
  // just dont return anything, when the post is to be hidden
  function yourTheme_geo_mashup_locations_json_filter($json_properties, $queried_object){
    $post_id = $queried_object->object_id;
    $meta_key = '_hide';  // the custom field "_hide" is a checkbox, that is "" when not used or "1" when checked.
    $meta_value = get_post_meta($post_id, $meta_key, true);
    
    if ( !filter_var($meta_value, FILTER_VALIDATE_BOOLEAN) ) { // using other types of custom fields. one needs different detection methods here
      return $json_properties;
    }
  }
  add_filter( 'geo_mashup_locations_json_object', 'yourTheme_geo_mashup_locations_json_filter', 10, 2 );  
?>
