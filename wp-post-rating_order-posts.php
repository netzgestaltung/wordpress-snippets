<?php 
/**
 * Get WP post rating - rated posts orderd
 * @author: Thomas Fellinger(@netzgestaltung)/Faisal Ahmed(@fftfaisal)
 * @license: GNU GPLv3
 * @required https://wordpress.org/plugins/wp-post-comment-rating/
 * @see idea: https://wordpress.org/support/topic/howto-sort-posts-by-rating/
 * @see resources about comments:
 * - https://wordpress.stackexchange.com/questions/59894/approve-comment-hook/59896
 * - https://developer.wordpress.org/reference/hooks/transition_comment_status/
 * - https://developer.wordpress.org/reference/hooks/comment_post/
 * - https://wordpress.stackexchange.com/questions/181881/wp-query-sort-by-comment-meta-data/182682
 * - https://developer.wordpress.org/reference/functions/get_comments/
 * - https://developer.wordpress.org/reference/hooks/wp_set_comment_status/
 *
 * @see resources about plugins:
 * - https://stackoverflow.com/questions/13452463/wordpress-how-to-get-plugin-options-from-other-plugin-page
 *
 * @see resources about posts:
 * - https://wordpress.stackexchange.com/questions/249881/multiple-custom-fields-for-orderby-in-wp-query
 * - https://make.wordpress.org/core/2015/03/30/query-improvements-in-wp-4-2-orderby-and-meta_query/
 * - https://developer.wordpress.org/reference/functions/update_post_meta/
 *
 * @usage set base value
 *        to properly use the orderby WP_Qery param all desired posts needs a base zero value
 *        customize the query args!
 *        only use once!
 *        insert into any page or post or archive template and load the page
 *   myPlugin_set_rating();
 *
 * @usage WP_Query
 *   $my_posts = new WP_Query(array('orderby' => 'meta_value_num', 'meta_key' => '_wpcr_rating', 'order' => 'ASC'));
 *
 * @usage filter 'pre_get_posts'
 *   function myPlugin_pre_get_posts($query) {
 *     if ( !is_admin() && $query->is_main_query() && is_archive() && $query->get('post_type') === 'my_post_type' ) {
 *       $query->set('meta_key','_wpcr_rating');
 *       $query->set('orderby','meta_value_num');
 *     }
 *   }
 *   add_action('pre_get_posts', 'myPlugin_pre_get_posts', 10, 1);
 * 
 * @usage multiple orderby (BETA, needs testing)
 *   $my_query_args = array(
 *     'orderby' => array( 
 *       'title_order' => 'ASC',
 *       'rating_order' => 'DESC',
 *     ), 
 *     'meta_query' => array(
 *       'relation' => 'AND',
 *       'title_order' => array(
 *           'key' => 'state',
 *           'value' => '-1',
 *           'compare' => 'NOT LIKE',
 *       ),
 *       'rating_order' => array(
 *           'key' => '_wpcr_rating',
 *           'value' => '-1',
 *           'compare' => 'NOT LIKE',
 *       ),
 *     ),
 *   ));
 *   $my_posts = new WP_Query($my_posts_args);
 */
// sets all existing posts '_wpcr_rating' meta field to zero, 
// customize the query args, only use once
function myPlugin_set_rating(){
  $args = array(
    'post_type' => 'my_post_type',
    'posts_per_page' => -1,
    'meta_query' => array(
      'rating_all' => array(
        'key' => '_wpcr_rating',
        'compare' => 'NOT EXISTS',
      ),
    ),
  );
  $query = new WP_Query($args);
  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      update_post_meta(get_the_ID(), '_wpcr_rating', 0);
    }
    wp_reset_postdata();
  }
}

// sets post_meta '_wpcr_rating' to zero when not having comments
function myPlugin_save_post($post_id, $post, $update){
  if ( !$post->_wpcr_rating ) {
    update_post_meta($post_id, '_wpcr_rating', 0);
  }
}
add_action('save_post', 'myPlugin_save_post', 10, 3);

// sets post_meta '_wpcr_rating' when new approved comment is posted
function myPlugin_comment_post($id, $approved){
  myPlugin_post_meta_avg_rating(get_comment($id)->comment_post_ID);
}
add_action('comment_post', 'myPlugin_comment_post', 10, 2);

// sets post_meta '_wpcr_rating' when wp_set_comment_status is called
function myPlugin_wp_set_comment_status($id, $status){
  myPlugin_post_meta_avg_rating(get_comment($id)->comment_post_ID);
}
add_action('wp_set_comment_status', 'myPlugin_wp_set_comment_status', 10, 2);

// sets post_meta '_wpcr_rating' when approved or deleted
function myPlugin_transition_comment_status($new_status, $old_status, $comment){
  myPlugin_post_meta_avg_rating($comment->comment_post_ID);
}
add_action('transition_comment_status', 'myPlugin_transition_comment_status', 10, 3);

// sets post_meta '_wpcr_rating'
function myPlugin_post_meta_avg_rating($post_id){
  $avg = myPlugin_calculate_avg_rating($post_id);
  if ( $avg > 0 ) {
    update_post_meta($post_id, '_wpcr_rating', $avg);
  }
}

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
  $a = shortcode_atts(array('avgText' => true, 'title' => ''), $atts);
  $wpcr_options = get_option('wpcr_settings');
  $post_id = get_the_ID();
  $avg = get_post_meta($post_id, '_wpcr_rating', true);
  // $avg = myPlugin_calculate_avg_rating($post_id); // now stored to post
  $comment_count = wp_count_comments($post_id)->approved;
  
  $output = '';
  $title = '';
  if ( $a['title'] !== '' ) {
    $title = '<span class="wpcr_title">' . $a['title'] . '</span>';
  }
  $avg_text = __('Average', 'wp-post-comment-rating');
  if ( $wpcr_options['wpcravg_text'] !== '' ) {
    $avg_text =  $wpcr_options['wpcravg_text'];
  }
  $avgText = __('average', 'wp-post-comment-rating');
  $outOf   = __('out of 5. Total', 'wp-post-comment-rating');
  
  if ( $avg > 0 ) {
    if ( filter_var($a['avgText'], FILTER_VALIDATE_BOOLEAN) ) {
      if ( $wpcr_options['tooltip_inline'] == 1 ) {
        $output = '<div class="wpcr_aggregate">';
        $output .= $title;
        $output .= '<a class="wpcr_tooltip" title="'.$avgText.': '.round($avg,2).' '.$outOf.': '.$comment_count.'"><span class="wpcr_stars" title="">'.$avg_text.':</span>';
        $output .= '<span class="wpcr_averageStars" data-wpcravg="' . $avg . '"></span></a></div>';
      }
      if ( $wpcr_options['tooltip_inline'] == 0 ) {
        $output = '<div class="wpcr_aggregate">';
        $output .= $title;
        $output .= '<a class="wpcr_inline" title=""><span class="wpcr_stars" title="">'.$avg_text.':</span>';
        $output .= '<span class="wpcr_averageStars" data-wpcravg="' . $avg . '"></span></a><span class="avg-inline">('.$avgText.': <strong> '.round($avg, 2).'</strong> '.$outOf.': '.$comment_count.')</span></div>';
      } 
    } else {
      $output = '<div class="wpcr_aggregate">' . $title . '<a class="wpcr_inline" title="' . $avg_text . ': ' . round($avg, 2) . '"><span class="wpcr_averageStars" data-wpcravg="' . $avg . '"></span></a></div>';
    }
  }
  return $output;
}
?>
