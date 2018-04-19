
<?php the_post(); // only inside the loop

  /**
   * split content and gallery
   * @see https://wordpress.stackexchange.com/a/282684/81728
   */
  $content = apply_filters('the_content', strip_shortcodes(get_the_content()));
  $gallery = get_post_gallery();

?>
...
<?php if ( !empty($content) ) { ?>
  <?php echo $content; ?>
<?php } ?>
...
<p>your special content between the_content and the_post_gallery</p>
...
<?php if ( !empty($gallery) ) { ?>
  <?php echo $gallery; ?>
<?php } ?>
