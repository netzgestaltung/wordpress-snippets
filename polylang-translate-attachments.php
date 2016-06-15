<?php

/** 
 * Translates all attachments in Wordpress Media Library
 * =====================================================
 * Be carefull, only run once!
 * Test first with an dummy image!
 */
function shamrock_translate_attachments($translateTo = 'en'){
   
  // Get all attachments 
  $attachmentsOptions = array(
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => -1, // set to 1 to test on only one image (the latest one)
  );
  $attachments = new WP_Query($attachmentsOptions);
    
  // Attachments loop
  if ( $attachments->have_posts() ) {

    while ( $attachments->have_posts() ) {
      
      $attachments->the_post();
      $translations = pll_get_post_translations(get_the_ID());

      // only for not translated attachments
      if ( !isset($translations[$translateTo]) ) {
			
        // Collect attachments Data
        $attachment = get_post();
        $attachmentLang = pll_get_post_language(get_the_ID());
        $attachmentParent = $attachment->post_parent;
        $attachmentMeta = get_post_meta(get_the_ID());
        
        // Set translations data
        $tranlatedData = (array) $attachment;
        $tranlatedData['ID'] = null;  // wp_insert_post() will create a new post
        $translatedLang = $translateTo;
        $translatedParent = pll_get_post($attachmentParent);
        $tranlatedData['post_parent'] = $tranlatedData['post_parent'] && $translatedParent ? $translatedParent : 0;
        
        // Create translated attachment        
        $translatedId = wp_insert_post($tranlatedData);
        pll_set_post_language($translatedId, $translatedLang);
        
        foreach ( $attachmentMeta as $metaKey => $metaValue ) {
          add_post_meta($translatedId, $metaKey, $metaValue[0]);
        }
			
        // Update translations
        $translations[$translatedLang] = $translatedId;
        pll_save_post_translations($translations);
			}
      // Debug
      // echo '<pre>', var_dump($translations), '</pre>';
    }
  }
  wp_reset_postdata();
}

?>
