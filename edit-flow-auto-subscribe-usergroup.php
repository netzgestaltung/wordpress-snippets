<?php

/**
 * Auto-subscribe and unsubscribe an Edit Flow user group when a post changes status
 *
 * @see http://editflow.org/extend/auto-subscribe-user-groups-for-notifications/
 *
 * @param string $new_status New post status
 * @param string $old_status Old post status (empty if the post was just created)
 * @param object $post The post being updated
 * @return bool $send_notif Return true to send the email notification, return false to not
 */
function yourTheme_auto_subscribe_usergroup( $new_status, $old_status, $post ) {
  global $edit_flow;

  // When the post is first created, you might want to automatically set
  // all of the user's user groups as following the post
  if ( 'draft' == $new_status ) {
    // Get all of the user groups for this post_author
    $user_ids_to_follow = array( $post->post_author );
    $user_ids_to_follow = array_map( 'intval', $user_ids_to_follow );
    $edit_flow->notifications->follow_post_user( $post->ID, $user_ids_to_follow, true );
  }

  // You could also follow a specific user group based on post_status
  if ( 'pending' == $new_status ) {
    // You'll need to get term IDs for your user groups and place them as
    // comma-separated values
    $usergroup_ids_to_follow = array(
      688,
    );
    $edit_flow->notifications->follow_post_usergroups( $post->ID, $usergroup_ids_to_follow, false );
  }
  
  /* Add more groups optionally
  // You could also follow a specific user group based on post_status
  if ( 'in-uebersetzung' == $new_status ) {
    // You'll need to get term IDs for your user groups and place them as
    // comma-separated values
    $usergroup_ids_to_follow = array(
      1767,
    );
    $edit_flow->notifications->follow_post_usergroups( $post->ID, $usergroup_ids_to_follow, false );
  }
  */

  // Return true to send the email notification
  return $new_status;
}

// Load edit flow extensions
add_filter( 'ef_notification_status_change', 'yourTheme_auto_subscribe_usergroup', 10, 3 );

?>
