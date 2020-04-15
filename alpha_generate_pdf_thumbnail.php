<?php
/**
 * Alpha Downloads Functions
 *
 * @package     Alpha Downloads
 * @subpackage  Includes/Functions
 * @since       1.0
*/

/**
 * Generate PDF Thumbnail
 *
 * Return the path to the generated thumbnail.
 *
 * @since   alpha 1.6
 * @source  https://gist.github.com/umidjons/11037635#gistcomment-3045106
 *
 * @param string $file url/path.
 * @param boolen $url return full icon url.
 * @return string path or url.
 */
function alpha_generate_pdf_thumbnail($source, $size=256, $page=1){
  // First, test Imagick's extension and classes.
	if ( !extension_loaded('imagick') || !class_exists('Imagick', false) ) {
		return false;
	}
	$img = false;

	if ( file_exists($source) && !is_dir($source) && mime_content_type($source) === 'application/pdf' ) { // source path must be available and not be a directory
		$size	= intval($size); // only use as integer, default is 256
		$page	= intval($page); // only use as integer, default is 1

		$page--; // default page 1, must be treated as 0 hereafter
		if ( $page < 0 ) {
		  $page = 0;
		} // we cannot have negative values

		$img = new Imagick($source."[$page]"); // [0] = first page, [1] = second page

    // measures
		$imH = $img->getImageHeight();
		$imW = $img->getImageWidth();
		if ( $imH == 0 ) { // if the pdf page has no height use 1 instead
		  $imH = 1;
		}
		if ( $imW == 0 ) {	// if the pdf page has no width use 1 instead
		  $imW = 1;
		}
		$sizR	=	round($size*(min($imW,$imH)/max($imW,$imH))); // relative pixels of the shorter side

    // attributes
		$img->setImageColorspace(255); // prevent image colors from inverting
		$img->setImageBackgroundColor('white'); // set background color and flatten
		$img	= 	$img->flattenImages(); // prevents black zones on transparency in pdf
		$img->setimageformat('jpeg');

    // cut
		if ($imH == $imW){$img->thumbnailimage($size,$size);}	// square page
		if ($imH < $imW) {$img->thumbnailimage($size,$sizR);}	// landscape page orientation
		if ($imH > $imW) {$img->thumbnailimage($sizR,$size);}	// portrait page orientation
	}
	return $img; 	// if the source file was not available, or Imagick didn't create a file returns false, otherwise the $img object
}

?>
