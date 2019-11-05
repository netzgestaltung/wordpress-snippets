/**
 * Get WP post rating - rated posts orderd
 * @reuired https://wordpress.org/plugins/wp-post-comment-rating/
 * @see https://wordpress.org/support/topic/howto-sort-posts-by-rating/
 */

// calculates average rating based on comment_meta 'rating'
function myPlugin_calculate_avg_rating($post_id = false){
  if ( !function_exists('wpcr_avg_rating') ) {
    return 0;
  }
  if ( !$post_id ) {
    $post_id = get_the_ID();
  }
  $comments = get_approved_comments($post_id);
  $sum = 0;
  $avg = 0;
  $count_rated = 0;
  
  foreach ( $comments as $comment ) {
    $rating = get_comment_meta($comment->comment_ID, 'rating', true);
    if ( $rating ) {
      $sum = $sum + (int)$rating;
      $count_rated++;
    }
  }
  if ( $count_rated > 0 ) { 
    $avg = $sum/$count_rated;
  }
  return $avg;
}
// add own rating avg function for better displaying
function myPlugin_avg_rating($atts) {
  if ( !function_exists('wpcr_avg_rating') ) {
    return '';
  }
  $a = shortcode_atts(array('title' => 'Rating',), $atts); // what is this good for?
  $output = '';
  
  $post_id = get_the_ID();
  $avg = myPlugin_calculate_avg_rating($post_id); // now stored to post
  $comment_count = wp_count_comments($post_id)->approved;
  
  $wpcr_options = get_option('wpcr_settings');
  $tooltip_inline = $wpcr_options['tooltip_inline'];
  $avgrating_text = $wpcr_options['wpcravg_text'];
  $avg_text = $avgrating_text == '' ? __( 'Average', 'wp-post-comment-rating' ) : $avgrating_text;
  $avgText = __('average', 'wp-post-comment-rating');
  $outOf   = __('out of 5. Total', 'wp-post-comment-rating');

  if ( $avg > 0 ) {
    if ( $tooltip_inline == 1 ) {
      $output = '<div class="wpcr_aggregate"><a class="wpcr_tooltip" title="'.$avgText.': '.round($avg,2).' '.$outOf.': '.$comment_count.'"><span class="wpcr_stars" title="">'.$avg_text.':</span>';
      $output .= '<span class="wpcr_averageStars" id="'.$avg.'"></span></a></div>';
    }
    if ( $tooltip_inline == 0 ) {
      $output = '<div class="wpcr_aggregate"><a class="wpcr_inline" title=""><span class="wpcr_stars" title="">'.$avg_text.':</span>';
      $output .= '<span class="wpcr_averageStars" id="'.$avg.'"></span></a><span class="avg-inline">('.$avgText.': <strong> '.round($avg, 2).'</strong> '.$outOf.': '.$comment_count.')</span></div>';
    }
  }
  return $output;
}
