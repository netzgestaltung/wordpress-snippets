<?php
/**
 * CF7 add dynamic taxonomy option to select menu fields in
 *
 * https://wordpress.stackexchange.com/questions/115947/contact-form-7-populate-select-list-with-taxonomy
 *
 * @usage [select name taxonomy:{$taxonomy} ...]
 * 
 * @param Array $tag  Contact form 7 field tag
 * 
 * @return Array $tag 
 */
add_filter('wpcf7_form_tag', 'myPlugin_wpcf7_form_tag');

function myPlugin_wpcf7_form_tag($tag){
  // Only run on select lists
  if ( $tag['type'] === 'select' || $tag['type'] === 'select*' ) {
    if ( !empty($tag['options']) ) {
      $terms_args = array();
      
      foreach( $tag['options'] as $option_string ) {
        // 0 = option key
        // 1 = option value - if any
        $option = explode(':', $option_string);
        
        if ( !empty($option) ) {
          if ( $option[0] === 'taxonomy' ) {
            $terms_args['taxonomy'] = $option[1];
            break;
          } else if ( $option[0] === 'parent' ) {
            $terms_args['parent'] = intval($option[1]);
            break;
          }
        }
      }
      if ( !empty($terms_args) ) {
      
        // Merge dynamic arguments with static arguments
        $terms_args = array_merge($terms_args, array(
          'hide_empty' => false,
        ));
        $terms = get_terms($terms_args);
        
        // Add terms to values
        if( !empty($terms) && !is_wp_error($terms) ) {
          foreach( $terms as $term ) {
            $tag['values'][] = $term->slug;
            $tag['labels'][] = $term->name;
          }
        }
      }
    }
  }
  return $tag;
}
?>
