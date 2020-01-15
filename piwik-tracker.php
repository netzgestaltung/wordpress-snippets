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
 * Add <?php yourTheme_piwik_tracker(); ?> to your header.php
 *
 * Configuration:
 * Specify $tracker_url, $piwik_site_id and $piwik_user_token
 *
 * Optional implement yourTheme_get_page_title() instead wp_title() from 
 * https://github.com/netzgestaltung/wordpress-snippets/blob/master/better-wordpress-title.php
 *
 * License: GNU General Public License v2.0
 */
function yourTheme_piwik_tracker(){
  
  // Config
  // Matomo base URL, for example http://example.org/piwik/ Must be set 
  $tracker_url = '';
  
  // Specify the site ID to track 
  $tracker_site_id = 1;
  
  // Specify an API token with at least Write permission, so the Visitor IP address can be recorded 
  // Learn more about token_auth: https://matomo.org/faq/general/faq_114/
  $tracker_user_token = '';
    
  // Only once a PageView
  if ( is_main_query() ) {
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
    
    // only track anonymous data!
    $piwikTracker->setIp('0.0.0.0');
    $piwikTracker->deleteCookies();
    $piwikTracker->disableCookieSupport();
    
    // Campaign Tracking
    if ( isset($_GET['c']) ) { 
      
      // Piwik related campain query params
      $campaign_parts = array(
        'pk_campaign',
        'pk_source',
        'pk_medium',
        'pk_kwd',
        'pk_content',
      );
      
      // get array from query string, delimited by "-"
      $campaign_params = explode('-', $_GET['c'], 5);
      // how much params are given?
      $campaign_param_length = count($campaign_params);
      // reduce the parts set to the number of given params
      $campaign_parts = array_slice($campaign_parts, 0, $campaign_param_length, true);
      // put params and parts together
      $campaign = array_combine($campaign_parts, $campaign_params);
      // create new querystring
      if ( count($campaign) > 0 ) { // if we have campain params
        $site_url .= '?' . http_build_query($campaign);
      }
    }
    // Errorpage handling
    // sends always /404 as url but adds a custom variable "404" that gets the insights 
    // of URLs leading to 404 pages
    if ( is_404() ) {
      $page_title = '404 not found, Look for 404 Data at Custom Variables';
      $piwikTracker->setCustomVariable(1, '404', $site_url, 'page');
      $site_url = $schema . $_SERVER['SERVER_NAME'] . '/404';
    }
    // Set the url of visited page that we send to matomo
    $piwikTracker->setUrl($site_url);
   
    // Sends Tracker request via http
    $piwikTracker->doTrackPageView($page_title);
    
    // You can also track Goal conversions
    // $piwikTracker->doTrackGoal($idGoal = 1, $revenue = 42);
  }
}

?>
