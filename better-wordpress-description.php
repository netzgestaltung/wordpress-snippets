
// Page description
function yourTheme_get_page_description($sep, $num_words, $meta = true){
  if ( !is_string($sep) || empty($num_words) ) { $sep = '-'; }
  if ( !is_numeric($num_words) || empty($num_words) ) { $num_words = 40; }
  
  $description = wp_title($sep, false);
  $sep = ' ' . $sep . ' ';
  
  if ( is_single() || is_page() ) {
    if ( has_excerpt() ) {
      $description .= get_the_excerpt();
    } else {
      $description .= get_the_content();
    }
  } else {
    if ( is_archive() ) {
      if ( is_tax() ) {
        $taxonomy = get_taxonomy(get_queried_object()->taxonomy);
        $description = $taxonomy->labels->singular_name . ' ' . __('archive', 'yourTheme') . $sep . single_term_title('', false);
        if ( !empty(term_description()) ) {
          $description .= $sep . term_description();
        }
      } else if ( is_tag() ) {
        $description = __('Tag archive', 'yourTheme') . $sep . single_tag_title('', false);
        if ( !empty(tag_description()) ) {
          $description .= $sep . tag_description();
        }
      } else if ( is_category() ) {
        $description = __('Category archive', 'yourTheme') . $sep . single_cat_title('', false);
        if ( !empty(category_description()) ) {
          $description .= $sep . category_description();
        }
      } else if ( is_author() ) {
        $description = __('Author archive', 'yourTheme') . $sep . get_the_author_meta('display_name');
        if ( !empty(get_the_author_meta('description')) ) {
          $description .= $sep . get_the_author_meta('description');
        }
      } else if ( is_home() && ( $posts_page = get_option( 'page_for_posts' ) ) ) {
        $description = get_the_title($posts_page);
      } else if ( is_search() ) {
        $description = __('Search Results For', 'sandbox').get_search_query();
      } 
    }
    $description .= $sep . get_bloginfo('description');
  }
  return wp_trim_words(filter_var($meta, FILTER_VALIDATE_BOOLEAN) ? wp_strip_all_tags($description, true) : $description, $num_words);
}
function yourTheme_the_page_description($sep, $num_words, $meta = true){
  echo yourTheme_get_page_description($sep, $num_words, $meta);
}
