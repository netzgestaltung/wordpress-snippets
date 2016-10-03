
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
  return filter_var($meta, FILTER_VALIDATE_BOOLEAN) ? wp_strip_all_tags($title) : $title;
}
function yourTheme_the_page_title($meta = true){
  echo yourTheme_get_page_title($meta);
}
