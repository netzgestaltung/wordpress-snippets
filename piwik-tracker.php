<?php

/**
 * Piwik Tracker implementing class PiwikTracker
 * =============================================
 * https://github.com/netzgestaltung/wordpress-snippets/blob/master/piwik-tracker.php
 * Tracks anonymous pageViews on every visit by HTTP Tracking API
 *
 * Usage:
 * Tracks campaigns with the scheme: https://www.domain.tld/?c=<pk_campaign>(-<pk_source>)(-<pk_medum>)(-<pk_keyword>)(-<pk_content>)
 *
 * Installation:
 * Download: https://github.com/matomo-org/matomo-php-tracker
 * save PiwikTracker.php in yourThemes <folderRoot>/includes/matomo-php-tracker/PiwikTracker.php
 * Integrate this file into yourThemes functions.php and rename "yourTheme" to your themes name
 *
 * Configuration:
 * Specify $tracker_url, $piwik_site_id and $piwik_user_token
 *
 * Optional implement yourTheme_page_title() instead wp_title() from 
 * https://github.com/netzgestaltung/wordpress-snippets/blob/master/better-wordpress-title.php
 *
 * License: GNU General Public License v2.0
 */
function yourTheme_piwik_tracker($query){
  
  // Config
  // Matomo base URL, for example http://example.org/piwik/ Must be set 
  $tracker_url = '';
  
  // Specify the site ID to track 
  $tracker_site_id = 1;
  
  // Specify an API token with at least Write permission, so the Visitor IP address can be recorded 
  // Learn more about token_auth: https://matomo.org/faq/general/faq_114/
  $tracker_user_token = '';
    
  // Only once a PageView
  if ( $query->is_main_query() ) {
    // page title
    // Use better page title: https://github.com/netzgestaltung/wordpress-snippets/blob/master/better-wordpress-title.php
    $page_title = wp_title('', false); // $page_title = yourTheme_get_page_title();
    $site_url = '';
    $schema = 'https://';
    if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] === 'off') {
      $schema = 'http://';
    }
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    $site_url .= $schema . $_SERVER['SERVER_NAME'] . $uri_parts[0];
    
    include_once(get_template_directory() . '/includes/matomo-php-tracker/PiwikTracker.php');
    PiwikTracker::$URL = $tracker_url;
    $piwikTracker = new PiwikTracker($tracker_site_id);

    // Specify an API token with at least Write permission, so the Visitor IP address can be recorded 
    // Learn more about token_auth: https://matomo.org/faq/general/faq_114/
    $piwikTracker->setTokenAuth($tracker_user_token);

    // You can manually set the visitor details (resolution, time, plugins, etc.) 
    // See all other ->set* functions available in the PiwikTracker.php file
    // $piwikTracker->setResolution(1600, 1400);
    
    // Campaign Tracking
    if ( isset($_GET['c']) ) { 
      $piwikTracker->setUrlReferrer($_SERVER['HTTP_REFERER']);
      
      $campaign_parts = array(
        'pk_campaign',
        'pk_source',
        'pk_medium',
        'pk_kwd',
        'pk_content',
      );
      $campaign_params = explode('-', $_GET['c'], 5);
      $campaign_param_length = count($campaign_params);
      $campaign_parts = array_slice($campaign_parts, 0, $campaign_param_length, true);
      $campaign = array_combine($campaign_parts, $campaign_params);
      if ( count($campaign) > 0 ) {
        $site_url .= '?' . http_build_query($campaign);
      }
    }
    $piwikTracker->setUrl($site_url);
   
    // Sends Tracker request via http
    $piwikTracker->doTrackPageView(page_title);
    
    // You can also track Goal conversions
    // $piwikTracker->doTrackGoal($idGoal = 1, $revenue = 42);
  }
}

add_action('pre_get_posts', 'yourTheme_piwik_tracker');

?>
