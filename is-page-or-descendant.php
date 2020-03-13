<?php
/**
 * is a given page ID the anchestor of the current page?
 */
function yourTheme_is_page_or_descendant($post_ID){
  $is_page_or_descendant = false;

  if ( is_page($post_ID) ) {
    $is_page_or_descendant = true;
  } elseif ( is_post_type_hierarchical(get_post_type()) ) {
    $post = get_post();
    $ancestors = get_post_ancestors($post->ID);

    if ( in_array($post_ID, $ancestors) ) {
      $is_page_or_descendant = true;
    }
  }
  return $is_page_or_descendant;
}
?>
