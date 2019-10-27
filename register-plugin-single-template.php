/**
 * Register single template for Download Post Type
 * @see
 * - https://wordpress.stackexchange.com/questions/17385/custom-post-type-templates-from-plugin-folder/350859#350859
 * - https://wpshout.com/hacking-the-wordpress-template-hierarchy/
 * @since  alpha 0.6.7
 */
function alpha_download_template($single_template) {
  global $alpha_options;
  
  $alpha_download_template = ALPHA_PLUGIN_DIR . 'templates/single-alpha_download.php';
  $use_template = ( $alpha_options['use_template'] && get_post_type() === 'alpha_download' && file_exists($alpha_download_template) );
  
  return $use_template ? $alpha_download_template: $single_template;
}
add_filter('single_template', 'alpha_download_template');
