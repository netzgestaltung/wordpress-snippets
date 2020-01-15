<?php 

// theme setup
add_action( 'after_setup_theme', 'myTheme_setup_theme' );

function myTheme_setup_theme() {
  // translate polylang strings
  add_action('admin_init', 'myTheme_string_translations');
  // translate post type descriptions
  add_filter('get_the_post_type_description', 'myTheme_post_type_description', 10, 2);
  // translate post type labels
  $post_types = get_post_types(array(
    'public'   => true,
    '_builtin' => false,
  ), 'objects');
  foreach ( $post_types as $post_type ) {
    add_filter( 'post_type_labels_' . $post_type->name, 'myTheme_post_type_labels');
  }
}

// translate post type descriptions
function myTheme_post_type_description($description, $post_type_obj){
  if ( function_exists('pll__') ) {
    // dont edit de, keeping one textfield
    if ( pll_current_language() !== 'de' ) {
      $translation = pll__($post_type_obj->name . '-description');
      if ( !empty($translation) && $translation !== $post_type_obj->name . '-description') {
        $description = $translation;
      }
    }
  }
  return $description;
}

// translate post type labels
function myTheme_post_type_labels($labels){
  if ( function_exists('pll__') ) {
    // dont edit de, keeping one textfield
    if ( pll_current_language() !== 'de' ) {
      $name = pll__($labels->name . '-label-name');
      if ( !empty($name) && $name !== $labels->name . '-label-name' ) {
        $labels->name = $name;
      }
      $singular_name = pll__($labels->singular_name . '-label-singular_name');
      if ( !empty($singular_name) && $singular_name !== $labels->singular_name . '-label-singular_name' ) {
        $labels->singular_name = $singular_name;
      }
    }
  }
  return $labels;
}
// translate polylang strings
function myTheme_string_translations(){
  // use identifier
  $strings = array(
    'mystring',
    'yourstring',
  );
  $post_types = get_post_types(array(
    'public'   => true,
    '_builtin' => false,
  ), 'objects');
  foreach ( $post_types as $post_type ) {
    $strings[] = $post_type->name . '-description';
    $strings[] = $post_type->labels->name . '-label-name';
    $strings[] = $post_type->labels->singular_name . '-label-singular_name';
  }
  if ( function_exists('pll_register_string') ) {
    foreach ( $strings as $string ) {
      pll_register_string('myTheme', $string, 'theme');
    }
  }
}
?>
