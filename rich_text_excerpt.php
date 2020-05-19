<?php
/**
 * based on https://gist.github.com/marcelotorres/c6164d6a9a8bfd0700bdb1de4de4fb6d
 */
add_action('add_meta_boxes', array('myPlugin_rich_text_excerpt', 'switch_boxes'));

/**
 * Add rich text editor to excerpt
 * Replaces the default excerpt editor with TinyMCE.
 */
class myPlugin_rich_text_excerpt{
  /**
   * Replaces the meta boxes.
   *
   * @return void
   */
  public static function switch_boxes()    {
    if ( !post_type_supports($GLOBALS['post']->post_type, 'excerpt') ) {
      return;
    }

    // ID, Screen(empty to support all post types), Context
    remove_meta_box('postexcerpt', '', 'normal');

    // Reusing just 'postexcerpt' doesn't work, but is not a problem
    // ID, Title, Display function, Screen(we use all screens with meta boxes), Context, Priority
    add_meta_box('postexcerpt2', __('Excerpt'), array( __CLASS__, 'show'), null, 'normal', 'core');
  }

  /**
   * Output for the meta box.
   *
   * @param  object $post
   * @return void
   */
  public static function show($post){

  ?><label class="screen-reader-text" for="excerpt"><?php _e('Excerpt') ?></label><?php

    // We use the default name, 'excerpt', so we don’t have to care about
    // saving, other filters etc.
    wp_editor(self::unescape($post->post_excerpt), 'excerpt', array(
      'textarea_rows' => 15,
      'media_buttons' => FALSE,
      'teeny' => TRUE,
      'tinymce'  => TRUE,
    ));
  }

  /**
   * The excerpt is escaped usually. This breaks the HTML editor.
   *
   * @param  string $str
   * @return string
   */
  public static function unescape($str){
    return str_replace(
      array('&lt;', '&gt;', '&quot;', '&amp;', '&nbsp;', '&amp;nbsp;'),
      array('<',    '>',    '"',      '&',     ' ',      ' '),
      $str
    );
  }
}
