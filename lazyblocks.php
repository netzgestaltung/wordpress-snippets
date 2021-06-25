/**
 * @file Lazy blocks snippets
 * @author Thomas Fellinger <office@netzgestaltung.at>
 * @license GPL-2.0-or-later
 * @link https://github.com/netzgestaltung/wordpress-snippets/blob/master/lazyblocks.php
 */

/**
 * remove lazy blocks wrapper container markup in frontend
 * https://lazyblocks.com/documentation/blocks-code/php-callback/
 */
add_filter('lzb/block_render/allow_wrapper', '__return_false');
  
/**
 * Additional lazy blocks Handlebars helper
 */
function myPlugin_lazyblocks_handlebars_helper($handlebars){

  /**
   * wp_get_attachment_image Handlebars helper
   * @link https://github.com/nk-o/lazy-blocks/issues/68
   * @see https://developer.wordpress.org/reference/functions/wp_get_attachment_image/
   * 
   * @example
   * {{{ wp_get_attachment_image control_name 'thumbnail' }}}
   */
  $handlebars->registerHelper('wp_get_attachment_image', function($image, $size=null){
    if ( isset($image['id']) ) {
      return wp_get_attachment_image($image['id'], $size);
    }	  
  });
}

// lazy block Handlebars helper
add_action('lzb_handlebars_object', 'myPlugin_lazyblocks_handlebars_helper');  
