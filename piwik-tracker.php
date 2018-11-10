<?php

/**
 * Piwik Tracker implementing class PiwikTracker
 * =============================================
 * Tracks anonymous pageViews on every visit by HTTP Tracking API
 * Tracks campaigns with the scheme: https://www.domain.tld/?c=<pk_campaign>(-<pk_source>)(-<pk_medum>)(-<pk_keyword>)(-<pk_content>)
 
 * Installation:
 * Download: https://github.com/matomo-org/matomo-php-tracker
 * save PiwikTracker.php in yourThemes <folderRoot>/includes/matomo-php-tracker/PiwikTracker.php
 *
 * Configuration:
 * Specify $tracker_url, $piwik_site_id and $piwik_user_token
 */

function yourTheme_piwik_tracker($query){
  
  // Config
  // Matomo base URL, for example http://example.org/piwik/ Must be set 
  $tracker_url = '';
  
  // Specify the site ID to track 
  $piwik_site_id = 1;
  
  // Specify an API token with at least Write permission, so the Visitor IP address can be recorded 
  // Learn more about token_auth: https://matomo.org/faq/general/faq_114/
  $piwik_user_token = '';
    
  
  if ( $query->is_main_query() ) {
    $site_url = '';
    $schema = 'https://';
    if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] === 'off') {
      $schema = 'http://';
    }
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    $site_url .= $schema . $_SERVER['SERVER_NAME'] . $uri_parts[0];
    
    include_once(get_template_directory() . '/includes/matomo-php-tracker/PiwikTracker.php');
    PiwikTracker::$URL = $tracker_url;
    $piwikTracker = new PiwikTracker($piwik_site_id);

    // Specify an API token with at least Write permission, so the Visitor IP address can be recorded 
    // Learn more about token_auth: https://matomo.org/faq/general/faq_114/
    $piwikTracker->setTokenAuth($piwik_user_token);

    // You can manually set the visitor details (resolution, time, plugins, etc.) 
    // See all other ->set* functions available in the PiwikTracker.php file
    // $piwikTracker->setResolution(1600, 1400);

    if (isset($_GET['c'])) { 
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
    // echo var_dump($campaign_url);
    $piwikTracker->setUrl($site_url);
    // Sends Tracker request via http
    $piwikTracker->doTrackPageView(sandbox_get_page_title());
    
    // You can also track Goal conversions
    // $piwikTracker->doTrackGoal($idGoal = 1, $revenue = 42);
  }
}

add_action('pre_get_posts', 'sandbox_piwik_tracker');

?>
