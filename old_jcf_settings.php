
<?php

/**
 * Used to migrate JCF old db table to a new version
 * @Deprecated
 * was only one time needed, usefull if you find an old version of JCF
 * @reference
 * - https://wordpress.org/support/topic/all-fields-gone-after-update-2/
 * - https://wordpress.org/support/topic/please-add-import-feature-from-versions-2-x/
 */

function old_jcf_settings($output_json = true){
  $post_types = get_post_types();
  $post_types_objects = get_post_types(null, null, 'objects');
  $jcf_fieldsets = 'jcf_fieldsets-';
  $jcf_fields = 'jcf_fields-';
  $jcf_settings = [];
  $new_jcf_settings = [];
  
  foreach ( $post_types as $post_type ) {
    $post_fieldsets = get_option($jcf_fieldsets . $post_type);
    $post_fields = get_option($jcf_fields . $post_type);
    
    if ( $post_fieldsets || $post_fields ) {
      $jcf_settings[$post_type] = [];
      if ( $post_fieldsets ) {
        $jcf_settings[$post_type]['fieldsets'] = $post_fieldsets;
        $new_jcf_settings['fieldsets'][$post_type] = array_merge(
          array('position' => null,'priority' => null),
          $post_fieldsets
        );
        foreach ( $post_fieldsets as $post_fieldset_id => $post_fieldset ) {
          $post_fieldset_fields = $post_fieldset['fields'];
          foreach ( $post_fieldset_fields as $post_fieldset_field => $enabled ) {
            $jcf_settings[$post_type]['fieldsets'][$post_fieldset_id]['fields'][$post_fieldset_field] = array_merge(
              $post_fields[$post_fieldset_field],
              array('enabled' => $enabled)
            );
          }
        }
      }
      if ( $post_fields ) {
        $new_jcf_settings['fields'] = [];
        $new_jcf_settings['fields'][$post_type] = $post_fields;
      }
      $new_jcf_settings['post_types'][$post_type] = $post_types_objects[$post_type];
    }
  }
  if ( $output_json ) {
    echo '<!-- old_jcf_settings: ', "\r\n";
    echo json_encode($jcf_settings), "\r\n";
    echo ' -->', "\r\n";
    
    echo '<!-- new_jcf_settings: ', "\r\n";
    echo json_encode($new_jcf_settings), "\r\n";
    echo ' -->', "\r\n";
  } else {
    echo '<!-- old_jcf_settings: ', "\r\n";
    echo var_dump($jcf_settings), "\r\n";
    echo ' -->', "\r\n";
    
    echo '<!-- new_jcf_settings: ', "\r\n";
    echo var_dump($new_jcf_settings), "\r\n";
    echo ' -->';
  }
}

?>
