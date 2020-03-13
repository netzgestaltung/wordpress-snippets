<?php
/**
 * adds login and out links to menus "site-loggedin" and "site-loggedout"
 * menu visibility is solved elsewhere (plugin widget_logic, nav menu widgets)
 */
function yourTheme_nav_menu_items($items, $args){
  if ( is_user_logged_in() ) {
    if ( $args->menu->slug === 'site-loggedin' ) {
      // add logout link to the end of the menu
      $logout_class = 'menu-item-logout menu-item menu-item-type-custom menu-item-object-custom';
      $items .= '<li class="' . $logout_class . '">' . wp_loginout(get_permalink(), false) . '</li>';
    }
  } elseif ( $args->menu->slug === 'site-loggedout' ) {
    // add login link to the begin of the menu
    $login_class = 'menu-item-login menu-item menu-item-type-custom menu-item-object-custom';
    $items = '<li class="' . $login_class . '">' . wp_loginout(get_permalink(), false) . '</li>' . $items;
  }
  return $items;
}
add_filter('wp_nav_menu_items', 'yourTheme_nav_menu_items', 10, 2);
?>
