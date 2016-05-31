<?php

/** 
 * Translates all attachments in Wordpress Media Library
 * =====================================================
 * Be carefull, only run once!
 * Test first with an dummy image!
 */
function YourTheme_translate_attachments($translateTo = 'en'){
   
  // Get all attachments 
  $attachmentsOptions = array(
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => -1,
  );
  $attachments = new WP_Query($attachmentsOptions);
  
  // Attachments loop
  if ( $attachments->have_posts() ) {

    while ( $attachments->have_posts() ) {
      $attachments->the_post();

      // Collect attachments Data
      $attachment = get_post();
      $attachmentLang = pll_get_post_language(get_the_ID());
      $attachmentParent = $attachment->post_parent;
      
      // Set translations data
      $tranlatedData = (array) $attachment;
      $tranlatedData['ID'] = null;  // wp_insert_post() will create a new post
      $translatedLang = $translateTo;      
      $translatedParent = pll_get_post($attachmentParent);   
      $tranlatedData['post_parent'] = $tranlatedData['post_parent'] && $translatedParent ? $translatedParent : 0;
      
      // Create translated attachment
      $translatedId = wp_insert_post($tranlatedData);
      pll_set_post_language($translatedId, $translatedLang);      
      add_post_meta($translatedId, '_wp_attachment_metadata', get_post_meta(get_the_ID(), '_wp_attachment_metadata', true));
	    add_post_meta($translatedId, '_wp_attached_file', get_post_meta(get_the_ID(), '_wp_attached_file', true));
	    
	    // Update translations
	    $translations = pll_get_post_translations( get_the_ID() );	    
	    $translations[$translatedLang] = $translatedId;	    
	    pll_save_post_translations($translations);
	    
	    // Debug
      // echo '<pre>', var_dump($translations), '</pre>';        
    }
  }
  wp_reset_postdata();

}

?>
