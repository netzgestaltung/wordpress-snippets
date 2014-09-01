<?php

  /**
   * custom social share widget
   * ==========================
   * No user tracking, no javascript.
   *
   * - to use in your themes function.php
   * - edit the markup of the widget at yourTheme_social();
   * - add your services at update()
   * - Service adresses are hardcoded and can change during time
   * - Add the CSS part of this file in yourThemes style.css
   */
  class YourTheme_social_widget extends WP_Widget {

    function YourTheme_social_widget() {
      /* Widget settings. */
      $widget_ops = array( 'classname' => 'social', 'description' => 'Display social share icons without automatic user tracking' );

      /* Widget control settings. */
      $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'social' );

      /* Create the widget. */
      $this->WP_Widget( 'social', 'Social share', $widget_ops, $control_ops );
    }

    // processes widget options to be saved
    function update($new_instance, $old_instance) {
      $new_instance = (array) $new_instance;

      $instance = array(
        'title' => strip_tags( $new_instance['title'] ),
        'services' => array(
          'facebook' => array(
            'name' => 'Facebook',
            'url' => 'https://www.facebook.com/sharer/sharer.php?u=',
          ),
          'twitter' => array(
            'name' => 'Twitter',
            'url' => 'http://twitter.com/share?url=',
          ),
          'googleplus' => array(
            'name' => 'Google+',
            'url' => 'https://plus.google.com/share?url=',
          ),
          'linkedin' => array(
            'name' => 'LinkedIn',
            'url' => 'http://www.linkedin.com/sharer.php?u=',
          ),
        ),
      );

      foreach ( $instance['services'] as $serviceName => $serviceData ) {
        $instance[$serviceName] = filter_var($new_instance[$serviceName], FILTER_VALIDATE_BOOLEAN);
      }

      return $instance;
    }

    // outputs the content of the widget
    function widget($args, $instance) {
      yourTheme_social($args, $instance);
    }

    // outputs the options form on admin
    function form($instance) {

      $defaults = array( 'facebook' => true, 'twitter' => true, 'googleplus' => true, 'linkedin' => false);
      $services = $instance['services'];
      $instance = wp_parse_args( (array) $instance, $defaults);
      $title = esc_attr($instance['title']);
  ?>
      <p>
      	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>
    <?php foreach ( $services as $serviceName => $serviceData ) { ?>
      <p>
        <input class="checkbox" value="true" type="checkbox" id="<?php echo $this->get_field_id($serviceName); ?>" name="<?php echo $this->get_field_name($serviceName); ?>" <?php checked($instance[$serviceName], true) ?> />
      	<label for="<?php echo $this->get_field_id($serviceName); ?>"><?php echo $serviceData['name']; ?></label>
      </p>
    <?php } ?>
  <?php
	  }

  }

  function yourTheme_social($args, $instance){
    extract($args);

    $title = apply_filters('widget_title', $instance['title'] );
    $services = $instance['services'];
    $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === false ? 'http' : 'https';
    $host = $_SERVER["HTTP_HOST"];
    $path = $_SERVER["REQUEST_URI"];
    $pageUrl =  $protocol . '://' . $host . $path;
    $servicesCount = 0;
    $servicesList = '';

    foreach ( $services as $serviceName => $serviceData ) {
      if ( $instance[$serviceName] ) {
        $servicesCount++;
        $servicesList .= '<li class="' . $serviceName . '"><a href="' . $serviceData["url"] . $pageUrl . '" target="_blank">' . $serviceData["name"] . '</a></li>';
      }
    }

    if ( $servicesCount > 0 ) {

      echo $before_widget;
    ?>
      <div class="social">
        <?php if ( $title ) { echo $before_title . $title . $after_title; } ?>
        <ul>
          <?php echo $servicesList; ?>
        </ul>
      </div>
    <?php
      echo $after_widget;

    }
  }

  function yourTheme_widgets_init(){;
    register_widget('YourTheme_social_widget');
  }

  add_action( 'widgets_init', 'yourTheme_widgets_init' );
  
?>
<style type="text/css">

/*!
 *  Font Awesome 4.2.0 by @davegandy - http://fontawesome.io - @fontawesome
 *  License - http://fontawesome.io/license (Font: SIL OFL 1.1, CSS: MIT License)
 */
/* FONT PATH
 * -------------------------- */
@font-face {
  font-family: 'FontAwesome';
  src: url('fonts/fontawesome-webfont.eot?v=4.2.0');
  src: url('fonts/fontawesome-webfont.eot?#iefix&v=4.2.0') format('embedded-opentype'),
       url('fonts/fontawesome-webfont.woff?v=4.2.0') format('woff'),
       url('fonts/fontawesome-webfont.ttf?v=4.2.0') format('truetype'),
       url('fonts/fontawesome-webfont.svg?v=4.2.0#fontawesomeregular') format('svg');
  font-weight: normal;
  font-style: normal;
}

.social li {
  float:left;
  margin:0 0.5em;
}
.social a{
  display:block;
  font-size:1.5em;
  text-indent:-999em;
  height:1em;
  width:1em;
}
.social a:before{
  display: block;
  float:left;
  height:1em;
  width:1em;
  text-align:center;
  text-indent:0;
  font-family:FontAwesome;
  font-size: inherit;
  text-rendering: auto;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.social .facebook   a:before{ content: "\f09a"; }
.social .twitter    a:before{ content: "\f099"; }
.social .googleplus a:before{ content: "\f0d5"; }
.social .linkedin   a:before{ content: "\f0e1"; }

</style>
