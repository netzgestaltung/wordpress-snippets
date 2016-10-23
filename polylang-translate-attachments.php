<?php

/** 
 * Translates all attachments in Wordpress Media Library
 * =====================================================
 * @depends   polylang plugin
 * @usage     add the function into a page or post template and call the page in your browser, 
 *            remove the function after all translations are done
 *            or put it inside an conditional comment to only translate in a certain condition
 * @example   if ( is_page('attachment-translation') ) { yourTheme_translate_attachments() }
 * @license   GPLv2
 * 
 * Test first with an dummy image!
 */
function yourTheme_translate_attachments(){
   
  // Get all attachments 
  $attachmentsOptions = array(
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => -1, // set to 1 to test on only one image (the latest one)
  );
  $attachments = new WP_Query($attachmentsOptions);
  $currentLang = pll_current_language();
  $languagesRaw = pll_the_languages(array('raw'=>1));
  $languages = array();
  foreach ( $languagesRaw as $language => $languageData ) {    
    if ( $languageData['slug'] != $currentLang ) {
      $languages[] = $languageData['slug'];
    }
  }
    
  // Attachments loop
  if ( $attachments->have_posts() ) {

    while ( $attachments->have_posts() ) {
      
      $attachments->the_post();
      $translations = pll_get_post_translations(get_the_ID());

      foreach ( $languages as $language) {
      
        // only for not translated attachments
        if ( !isset($translations[$language]) ) {

          // Collect attachments Data
          $attachment = get_post();
          $attachmentLang = pll_get_post_language(get_the_ID());
          $attachmentParent = $attachment->post_parent;
          $attachmentMeta = get_post_meta(get_the_ID());
          
          // Set translations data
          $tranlatedData = (array) $attachment;
          $tranlatedData['ID'] = null;  // wp_insert_post() will create a new post
          $translatedParent = pll_get_post($attachmentParent);
          $tranlatedData['post_parent'] = $tranlatedData['post_parent'] && $translatedParent ? $translatedParent : 0;
          
          // Create translated attachment        
          $translatedId = wp_insert_post($tranlatedData);
          pll_set_post_language($translatedId, $language);
          
          foreach ( $attachmentMeta as $metaKey => $metaValue ) {
            add_post_meta($translatedId, $metaKey, $metaValue[0]);
          }
			
          // Update translations
          $translations[$language] = $translatedId;
          pll_save_post_translations($translations);

			  }
			}
      // Debug
      // echo '<pre>', var_dump($translations), '</pre>';
      
    }
  }
  wp_reset_postdata();
}

?>
