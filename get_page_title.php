<?php 
/**
 * Generates a usefull title for all fontend pages, posts, archives, search, 404 and custom post_type/taxonomy pages/archives
 * @Deprecated
 * Use wp_get_document_title() instead or add_theme_support('title-tag');
 * if neccesarry use one of the filters: 
 * 'pre_get_document_title' -> overwrites the title complete
 * 'document_title_separator' -> set the separator
 * 'document_title_parts' -> overwrite parts, array will be imploded and security filtered
 * @reference: https://developer.wordpress.org/reference/functions/wp_get_document_title/
 */

// Page title
function yourTheme_get_page_title($meta = true){
  $title = wp_title('', false); 
  
  if ( is_search() ) {
    $title = __('Search for', 'sandbox') . ' ' . get_search_query();
  }
  if ( is_404() ) {
    $title = __('Not found', 'sandbox');
  }
  if ( empty($title) ) {
    $title = get_bloginfo('name') . ' - ' . get_bloginfo('description');
  } else {
    $title .= ' - ' . get_bloginfo('name');
  }
  return filter_var($meta, FILTER_VALIDATE_BOOLEAN) ? esc_html(wp_strip_all_tags($title)) : $title;
}
function yourTheme_the_page_title($meta = true){
  echo yourTheme_get_page_title($meta);
}
?>
