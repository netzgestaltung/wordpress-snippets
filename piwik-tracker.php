
add_action('wp_footer', 'myPlugin_piwik_tracker');
// track download actions
// used with alpha_downloads: https://github.com/netzgestaltung/alpha-downloads
// add_action('ddownload_save_success_before', 'myPlugin_action_tracker');


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
  * Integrate this file into yourThemes functions.php and rename "myPlugin" to your themes name
  *
  * Configuration:
  * Specify $tracker_url, $piwik_site_id and $piwik_user_token
  *
  *
  * License: GNU General Public License v2.0
  */
function myPlugin_get_matomo_tracker(){
  // Config
  // Matomo base URL, for example http://example.org/piwik/ Must be set
  $tracker_url = '';
  // Specify the site ID to track
  $tracker_site_id = 1;
  // Specify an API token with at least Write permission, so the Visitor IP address can be recorded
  // Learn more about token_auth: https://matomo.org/faq/general/faq_114/
  $tracker_user_token = '';
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

  return $piwikTracker;
}
function myPlugin_action_tracker($download_id){
  $piwikTracker = myPlugin_get_matomo_tracker();
  // requires "alpha-downloads"
  $download_url = get_post_meta($download_id, '_alpha_file_url', true);
  $piwikTracker->doTrackAction($download_url, 'download');
}
function myPlugin_piwik_tracker(){
  // exclude json calls
  if ( isset($_POST['json']) || isset($_GET['json']) ) {
    return;
  }
  // page url
  $site_url = '';
  $schema = 'https://';
  if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] === 'off') {
    $schema = 'http://';
  }
  $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
  $site_url .= $schema . $_SERVER['SERVER_NAME'] . $uri_parts[0];

  $is_referer = isset($_SERVER['HTTP_REFERER']) ? ( $site_url === $_SERVER['HTTP_REFERER'] ) : false;

  // exclude:
  // - site_url ist referer
  // - admin pages
  // - is outside main query
  // - is wp doing ajax
  // - bot user agents by regex
  if ( !$is_referer && !is_admin() && is_main_query() && !wp_doing_ajax() && !preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']) ) {

    $piwikTracker = myPlugin_get_matomo_tracker();
    // page title
    $page_title = wp_get_document_title();

    if ( is_404() ) {
      $page_title = '404 not found, Look for 404 Data at Custom Variables';
      $piwikTracker->setCustomVariable(1, '404', $site_url, 'page');
      $site_url = $schema . $_SERVER['SERVER_NAME'] . '/404';
    }

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

    // Event tracking
    // ?mtb={category}-{action}-{name}
    // we map short URL IDs to readable Values
    // asking for spezific values is very secure but hard to maintain
    if ( isset($_GET['mtb']) ) {
      $mtb_params = explode('-', $_GET['mtb'], 3);
      $mtb_value = false;
      if ( is_404() ) {
        $mtb_value = true;
      }
      // Homepage mapping
      if ( $mtb_params[0] === 'h' ) {
        $mtb_params[0] = 'Home';
      }
      // Type mapping
      if ( $mtb_params[1] === 'b' ) {
        $mtb_params[1] = 'Button';
      } else if ( $mtb_params[1] === 'l' ) {
        $mtb_params[1] = 'Link';
      } else if ( $mtb_params[1] === 't' ) {
        $mtb_params[1] = 'Thumbnail';
      }
      // Content Identifier mapping
      if ( $mtb_params[2] === 'my-button1' ) {
        $mtb_params[2] = 'My first button';
      } else if ( $mtb_params[2] === 'my-link2' ) {
        $mtb_params[2] = 'My second link';
      }
      // track event
      $piwikTracker->doTrackEvent($mtb_params[0], $mtb_params[1], $mtb_params[2], $mtb_value);
    }

    // Set the url of visited page that we send to matomo
    $piwikTracker->setUrl($site_url);

    // Sends Tracker request via http
    $piwikTracker->doTrackPageView($page_title);
    // You can also track Goal conversions
    // $piwikTracker->doTrackGoal($idGoal = 1, $revenue = 42);
  }
}
