<?php
  /*
   * Missing wordpress template functions: the_slug()/get_the_slug()
   * ===============================================================
   * Forked from: https://gist.github.com/greglinch/4212235
   * Original Credit: http://www.tcbarrett.com/2011/09/wordpress-the_slug-get-post-slug-function/
   */
  
  /* get_the_slug
   * returns the page/post/term/category/tag/author slug
   */
  function get_the_slug(){
    $slug = basename(get_permalink());
    do_action('before_slug', $slug);
    $slug = apply_filters('slug_filter', $slug);
    do_action('after_slug', $slug);
    return $slug;
  }
  
  /* the_slug
   * echoes get_the_slug
   */
  function the_slug(){
    echo get_the_slug();
  }
  
?>
