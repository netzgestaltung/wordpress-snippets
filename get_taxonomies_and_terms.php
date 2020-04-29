<?php
// Get all taxonomies for a post and its terms
// works like get_the_terms() but returns an array of all taxonomies that are registered to the post.
function sandbox_get_the_taxonomies_and_terms($post_id=0){
  $post_id = absint($post_id);
  if ( !$post_id ) {
    $post_id = get_the_ID();
  }
  $post = get_post($post_id);
  $taxonomy_objects = get_object_taxonomies($post, 'objects');
  $taxonomies = array();
  $taxonomies_index = 0;

  foreach ( $taxonomy_objects as $taxonomy_object ) {
    $taxonomy_term_objects = get_the_terms($post_id, $taxonomy_object->name);
    if ( !empty($taxonomy_term_objects) && !is_wp_error($taxonomy_term_objects) ) {
      $taxonomies[$taxonomy_object->name] = array(
        'slug' =>$taxonomy_object->name,
        'label' =>$taxonomy_object->label,
        'terms' => array(),
      );
      foreach ( $taxonomy_term_objects as $taxonomy_term_object ) {
        $taxonomies[$taxonomy_object->name]['terms'][$taxonomy_term_object->slug] = array(
          'slug' => $taxonomy_term_object->slug,
          'label' => $taxonomy_term_object->name,
        );
      }
      $taxonomies_index++;
    }
  }
  return $taxonomies;
}
?>
