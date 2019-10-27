
/**
 * Redirect single download post request when not allowed
 * Redirect taxonomy archive pages when not allowed
 * @see
 * - https://wpshout.com/hacking-the-wordpress-template-hierarchy/
 * - permission handling from /includes/process-download.php method alpha_download_process
 * @since  alpha 0.6.7
 */
function alpha_download_template_redirect() {
  $redirect = false;
  
	// Check only single post request for alpha_download post type
	if ( ( is_single() || is_archive() ) && get_post_type() === 'alpha_download' ) {
    global $alpha_options;    
    $redirect_ID = $alpha_options['members_only_redirect'];
    
    if ( is_single() ) {
      $post = get_post();
	    $file_options = get_post_meta($post->ID, '_alpha_file_options', true);

	   	// Check for members only
	    if ( !alpha_download_permission($file_options) ) {      
		    do_action('ddownload_download_permission', $post->ID);
		    
	      // Get redirect location
	      if ( isset($file_options['members_only_redirect']) ) {
	        $redirect_ID = $file_options['members_only_redirect'];
        }
        $redirect = true;
      }
    }
    if ( is_archive() && !alpha_download_permission($alpha_options) ) {
      $redirect = true;
    }
  }
  if ( $redirect ) {
	  // Try to redirect
	  if ( $redirect_location = get_permalink($redirect_ID) ) {
		  wp_safe_redirect($redirect_location);
		  exit();
	  } else {
		  // Invalid page provided, show error message
		  wp_die(__('Please login to download this file!', 'alpha-downloads'));
	  }
  }
}
add_action('template_redirect', 'alpha_download_template_redirect');
