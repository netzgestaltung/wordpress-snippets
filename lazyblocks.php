
// remove lazy block wrapper container markup in frontend
// https://lazyblocks.com/documentation/blocks-code/php-callback/
add_filter('lzb/block_render/allow_wrapper', '__return_false');

// lazy block Handlebars helper
add_action('lzb_handlebars_object', 'myPlugin_lazyblock_handlebars_helper');
  
/**
 * lazy block Handlebars helper
 * 
 * image sizes helper
 * https://github.com/nk-o/lazy-blocks/issues/68
 */
function myPlugin_lazyblock_handlebars_helper($handlebars){
  $handlebars->registerHelper('wp_get_attachment_image', function($image, $size=null){
    if ( isset($image['id']) ) {
      return wp_get_attachment_image($image['id'], $size);
    }	  
  });
}
  
