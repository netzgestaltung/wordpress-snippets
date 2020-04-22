<?php
/**
 * Generate PDF Thumbnail
 *
 * Return the path to the generated thumbnail.
 * Use for custom PDF upload meta_box fields.
 * Ghostscript is used as execution command.
 * Its for shared hoster that does not support imagick but has ghostscript installed
 *
 * @since   alpha 1.6
 * @source  https://gist.github.com/umidjons/11037635#gistcomment-3045106
 *
 * @param string  $source path to source file.
 * @param integer $width width of the desired output image (at 72dpi)
 * @return string path or url.
 */
function myPlugin_generate_pdf_thumbnail($source, $width=252){
  // First, test ghostscripts existance
  exec('gs --help', $gs_help, $gs_check);
  if ( $gs_check !== 0 ) { // sorry, no ghostscript installed
    return false;
  }
  $img = false;
  $format = 'jpeg';

  // source path must be available and not be a directory, mime type: application/pdf
  if ( file_exists($source) && !is_dir($source) && mime_content_type($source) === 'application/pdf' ) {
    $width  = intval($width); // only use as integer, default is 256
    $height = $width*1.4142; // ISO 216 / DIN 476 / A4

    // $img = wp_get_image_editor($source);
    // $img = new Imagick($source . '[' . $page . ']'); // [0] = first page, [1] = second page
    $path_parts = pathinfo($source);
    $img_path = $source . '.' . $format;

    $ghostscript = 'gs -sDEVICE=' . $format . ' -dJPEGQ=75 -r72x72 -dBATCH -dNOPAUSE -dFirstPage=1 -dLastPage=1 -dPDFFitPage=true -dDEVICEWIDTHPOINTS=' . $width . ' -dDEVICEHEIGHTPOINTS=' . $height . ' -sOutputFile=' . $img_path . ' ' . $source;
    $ghostscript = exec($ghostscript, $gs_convert, $gs_convert_check);
    if ( $gs_convert_check === 0 ) {
      $img = $img_path;
    }
  }
  return $img;   // if the source file was not available, or Imagick didn't create a file returns false, otherwise the $img object
}

/**
 * Set Post Thumbnail from PDF Thumbnail
 * 
 * takes an image path and sets it as post thumbnail for a given post ID
 * used in conjunction with "myPlugin_generate_pdf_thumbnail()" you get
 * post thumbnails automagically from a PDF upload custom field.
 *
 * @since   alpha 1.6
 * @source  https://www.wpexplorer.com/wordpress-featured-image-url/
 *
 * @param  integer $post_id, default is 0
 * @param  string  $pdf_thumbnail path to the temporary pdf thumbnail
 * @param  boolean $delete_tmp wheter the temporary pdf thumbnail should be deleted, default is true
 * @return array
 * - the path to the post thumbnail
 * - the html for the post thumbnail meta box
 * - result of temporary file deletion
 */
function myPlugin_set_post_thumbnail($post_id=0, $pdf_thumbnail, $delete_tmp=true){
  $post_id = intval($post_id) > 0 ? intval($post_id) : 0;
  $post_thumbnail = false;

  if ( file_exists($pdf_thumbnail) && !is_dir($pdf_thumbnail) && mime_content_type($pdf_thumbnail) === 'image/jpeg' ) {
    $pdf_thumbnail_pathinfo = pathinfo($pdf_thumbnail);
    $pdf_thumbnail_img = file_get_contents($pdf_thumbnail);
    $unique_thumbnail_name = wp_unique_filename( $upload_dir['path'], $pdf_thumbnail_pathinfo['basename'] ); // Generate unique name
    $post_thumbnail_name = basename($unique_thumbnail_name); // Create image file name
    $upload_dir = wp_upload_dir();

    // Check folder permission and define file location
    if( wp_mkdir_p($upload_dir['path']) ) {
      $post_thumbnail = $upload_dir['path'] . '/' . $post_thumbnail_name;
    } else {
      $post_thumbnail = $upload_dir['basedir'] . '/' . $post_thumbnail_name;
    }

    // Check image file type
    $post_thumbnail_filetype = wp_check_filetype($post_thumbnail_name, null);

    // Create the image  file on the server
    file_put_contents($post_thumbnail, $pdf_thumbnail_img);

    // Set attachment data
    $post_thumbnail_attachment = array(
      'post_mime_type' => $post_thumbnail_filetype['type'],
      'post_title'     => sanitize_file_name($post_thumbnail_name),
      'post_content'   => '',
      'post_status'    => 'inherit'
    );
    // Create the attachment
    $post_thumbnail_id = wp_insert_attachment($post_thumbnail_attachment, $post_thumbnail, $post_id);

    // Define attachment metadata
    $post_thumbnail_data = wp_generate_attachment_metadata($post_thumbnail_id, $post_thumbnail);

    // Assign metadata to attachment
    wp_update_attachment_metadata($post_thumbnail_id, $post_thumbnail_data);

    // And finally assign featured image to post
    if ( set_post_thumbnail($post_id, $post_thumbnail_id) ) {
      // delete temporary file if the option is set (default)
      if ( $delete_tmp ) {
        $delete_tmp = wp_delete_file($pdf_thumbnail);
      }
      $post_thumbnail = array(
        'file' => $post_thumbnail,
        // backend-js ajax callback uses WPSetThumbnailHTML(html) to replace the meta box
        // it is then displayed immediatly and saved correctly when the post gets updated
        // @see  https://developer.wordpress.org/reference/functions/_wp_post_thumbnail_html/
        // @see  https://github.com/WordPress/WordPress/blob/master/wp-admin/js/post.js#L111
        // @example  https://github.com/WordPress/WordPress/blob/master/wp-admin/js/set-post-thumbnail.js
        'meta_box' => _wp_post_thumbnail_html($post_thumbnail_id, $post_id),
        'delete_tmp' => $delete_tmp,
      );
    }
  }
  return $post_thumbnail;
}
/**
 * Generate and set all post thumbnails for a post type by a pdf upload url field
 *
 * use once to generate post thumbnails for previously created download posts
 * does not overwrite existing post thumbnails so a second run is save
 * 
 * @uses
 * - myPlugin_generate_pdf_thumbnail()
 * - myPlugin_set_post_thumbnail()
 *
 * @param  $post_type   string post type of posts to generate the thumbnail for
 * @param  $field_name  string the name of the custom meta field
 * @return $result      array  array with error handling, message and an array "set" with complete details over every set thumbnail
 */
function myPlugin_set_all_post_thumbnails($post_type, $field_name){
  $result = array('set'=>false, 'error'=>true, 'message'=>'no post type entered or field name entered');
  if ( !empty($post_type) && !empty($field_name) ) {
    $result['message'] = 'no posts found in "' . $post_type . '"';
    $posts = new WP_Query(array(
      'post_type' => $post_type,
      'posts_per_page' => -1,
    ));
    if ( $posts->have_posts() ) {
      $result['message'] = 'adding array "set", starting index at 0';
      $result['set'] = array();
      $index = 0;

      while ( $posts->have_posts() ) {
        $posts->the_post();
        $post_custom = get_post_custom();
        $post_id = get_the_ID();
        $file_url = isset($post_custom[$field_name]) ? $post_custom[$field_name][0] : '';

        $result['set'][$index] = array(
          'post_id' => $post_id,
          'file_url' => $file_url,
          'success' => false,
          'message' => 'file field' . $field_name . ' is empty',
        );

        if ( !empty($file_url) ) {
          $result['set'][$index]['message'] = 'post thumbnail allready set';
          $file_path = trailingslashit(ABSPATH) . ltrim(parse_url($file_url, PHP_URL_PATH), '/');
          $result['set'][$index]['file_path'] = $file_path;

          if ( !has_post_thumbnail($post_id) ) {
            $result['set'][$index]['message'] = 'pdf file not found or no pdf file source';
	          $pdf_thumbnail = myPlugin_generate_pdf_thumbnail($file_path);
            $result['set'][$index]['pdf_thumbnail'] = $pdf_thumbnail;

	          if ( $pdf_thumbnail !== false ) {
	            $result['set'][$index]['message'] = 'pdf thumbnail not found or no jpeg file source';
	            $post_thumbnail = myPlugin_set_post_thumbnail($post_id, $pdf_thumbnail);
              $result['set'][$index]['post_thumbnail'] = $post_thumbnail;

	            if ( $post_thumbnail !== false ) {
	              $result['set'][$index]['success'] = true;
	              $result['set'][$index]['message'] = 'post thumbnail set succesful';

	              if ( $result['error'] === true ) {
	                $result['error'] = false;
	              }
	            }
	          }
          }
        }
        $index++;
      }
      $result['message'] .= ', total index: ' . $index;
    }
  }
  return $result;
}

?>
