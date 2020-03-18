<?php

/**
 * Buddypress helper functions
 * ===========================
 */

// Page is buddypress?
function myPrefix_bp_is_buddypress(){
  return ( !bp_is_blog_page() );
}

// returns true if a page is buddy presss and the user is logged in
function myPrefix_bp_is_restricted(){
  return ( !is_user_logged_in() && myPrefix_bp_is_buddypress() && !bp_is_register_page() && !bp_is_activation_page() );
}

// returns true if a page is an other members front or subpage
function myPrefix_bp_is_other_user(){
  return ( bp_is_user() && !bp_is_my_profile() );
}

/**
 * Buddypress action and filter functions
 * =====================================
 */

// action template_redirect
function myPrefix_bp_template_redirect() {
  // buddypress walled garden - community can only be watched logged in
  if ( myPrefix_bp_is_restricted() ) {
    bp_core_redirect();
  }
  // hide members overview from all exept super admin
  if ( bp_is_members_directory() && !is_super_admin() ) {
    wp_redirect(bp_loggedin_user_domain());
    exit;
  }
  // hide other members pages from users who cant manage options
	if ( !current_user_can('manage_options') ) {
		if ( myPrefix_bp_is_other_user() ) {
			wp_redirect(home_url());
			exit;
		}
	}
}

// action wp_enqueue_scripts
function myPrefix_bp_enqueue_scripts() {
  if ( !is_admin() ) {
    // Dequeue BP Nouveau style
    wp_dequeue_style('bp-nouveau');
    wp_deregister_style('bp-nouveau');
    wp_deregister_script('bp-nouveau');

    // dequeue oder scripts and styles
    wp_deregister_script('bp-confirm');
    wp_deregister_script('bp-widget-members');
    wp_deregister_script('bp-jquery-query');
    wp_deregister_script('bp-jquery-cookie');
    wp_deregister_script('bp-jquery-scroll-to');
      wp_deregister_style('bp-admin-bar');
      wp_deregister_style('bp-avatar');
    if ( !myPrefix_bp_is_buddypress() ) {
      wp_deregister_script('jquery-caret');
      wp_deregister_script('jquery-atwho');
      wp_deregister_script('bp-plupload');
      wp_deregister_script('bp-avatar');
      wp_deregister_script('bp-webcam');
      wp_deregister_script('bp-cover-image');
      wp_deregister_script('bp-moment');
      wp_deregister_script('bp-livestamp');
      wp_deregister_script('bp-moment-locale');
      wp_deregister_script('bp-webcam');
    }
  }
}

// action bp_template_include_reset_dummy_post_data
function myPrefix_bp_template_include_reset_dummy_post_data() {
  //Change the Heading
  if ( !bp_is_theme_compat_active() || !is_singular() ) {
    return ;
  }
  global $wp_query, $post;
  $post->post_title = _x('Profile', 'Member profile main navigation', 'buddypress');
}

// action bp_get_title_parts
function myPrefix_bp_get_title_parts($bp_title_parts) {
  // Update the Broser page title
  if ( bp_is_directory() || bp_is_register_page() || bp_is_activation_page() ) {
    // No current component (when does this happen?).
    $bp_title_parts = array(get_the_title(get_queried_object_id()));
  }
  return $bp_title_parts;
}

// action bp_loaded
function myPrefix_bp_loaded() {
  // disable registration bp
  remove_action('bp_init', 'bp_core_wpsignup_redirect');
  remove_action('bp_screens', 'bp_core_screen_signup');
  // remove_action('bp_screens', 'bp_core_screen_signup');
}

// filter bp_nouveau_get_member_meta
function myPrefix_bp_nouveau_get_member_meta(){

}

// filter wp_setup_nav_menu_item
function myPrefix_bp_nav_menu_item($menu_item){
  // Change profile nav URL to users front page
  if ( is_user_logged_in() && !is_admin() ) {
    $remove = 'profile/';
    if ( substr_compare($menu_item->url, $remove, -strlen($remove)) === 0 ) {
      $menu_item->url = bp_loggedin_user_domain();
    }
  }
  // echo var_dump($menu_item);
  return $menu_item;
}
// filter bp_get_signup_page
function myPrefix_bp_get_signup_page($page ){
  return bp_get_root_domain() . '/wp-login.php?action=register';
}

// filter bp_members_edit_profile_url
function myPrefix_bp_members_edit_profile_url($url){
  if ( class_exists('WP_Frontend_Profile') ) {
    $page_id = wpfep_get_option('profile_edit_page', 'wpfep_pages', false);

    if ( $page_id !== false ) {
	    $url = get_permalink( $page_id );
    }
  }
  return $url;
}

/**
 * Buddypress action and filter setup
 * =================================
 */
add_action('bp_loaded', 'myPrefix_bp_loaded');
add_action('template_redirect', 'myPrefix_bp_template_redirect', 30);
add_action('wp_enqueue_scripts', 'myPrefix_bp_enqueue_scripts', 30);
add_action('bp_template_include_reset_dummy_post_data', 'myPrefix_bp_template_include_reset_dummy_post_data', 30);

add_filter('bp_get_title_parts', 'myPrefix_bp_get_title_parts', 30);
// add_filter('bp_nouveau_get_member_meta', 'myPrefix_bp_nouveau_get_member_meta', 30);
add_filter('wp_setup_nav_menu_item', 'myPrefix_bp_nav_menu_item', 30);
add_filter('bp_get_signup_page', 'myPrefix_bp_get_signup_page');
add_filter('bp_members_edit_profile_url', 'myPrefix_bp_members_edit_profile_url');

?>
