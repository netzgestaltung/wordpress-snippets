<?php

/**
 * Hide the "Publish" button until a post is ready to be published
 * In this example, we only show the "Publish button" until the post has the "Pending" status
 *
 * @see http://editflow.org/extend/hide-the-publish-button-for-certain-custom-statuses/
 */
function yourTheme_hide_publish_button_until() {
 
  if ( ! function_exists( 'EditFlow' ) )
    return;

  if ( ! EditFlow()->custom_status->is_whitelisted_page() )
    return;

  // Show the publish button if the post has one of these statuses
  $show_publish_button_for_status = array(
    'metadatencheck',
    // The statuses below are WordPress' public statuses
    'future',
    'publish',
    'schedule'
  );
  if ( ! in_array( get_post_status(), $show_publish_button_for_status ) ) {
    ?>
    <style>
      #publishing-action { display: none; }
    </style>
    <?php
  }
}

// Load edit flow extensions
add_action( 'admin_head', 'yourTheme_hide_publish_button_until' );

?>
