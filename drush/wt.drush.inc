<?php
/**
 * Implements hook_drush_command().
 */
function wt_drush_command() {
  $items = array();

  $items['wt-site-setup'] = array(
    'description' => dt('Sets up the config files and settings values for a new Wildlife Trust site.'),
    'arguments' => array(
      'site_name' => dt('The name of the new site.'),
    ),
    'options' => array(
      'site_email' => dt('The email address for the site (required).'),
      'admin_user_email' => dt('The email address for the admin user (required).'),
      'strapline' => dt('The strapline for the site (optional, defaults to the site name).'),
      'hub_url' => dt('The URL of the hub site (required for local sites only).'),
      'typekit_id' => dt('The TypeKit ID for the site.'),
      'google_api' => dt('The Google API key for the site.'),
    ),
    'examples' => array(
      'drush wt-site-setup "Site Name" --site_email="support@example.com" --admin_user_email="admin@domain.com" --typekit_id="123456"' => dt('Sets up the site "Site Name" with options as specified.'),
    ),
    'aliases' => array('wt-setup'),
  );

  $items['wt-register-local'] = array(
    'description' => dt('Only run on the National (Hub) site; registers a new Local site with the content sharing mechanism.'),
    'arguments' => array(
      'site_name' => dt('The name of the new local site, as it should appear in search results, etc.'),
    ),
    'options' => array(
      'url' => dt('URL of the new local site (required).'),
    ),
    'examples' => array(
      'drush wt-register-local "Local Trust Site" --url="http://www.localsite.org"' => dt('Registers the new "Local Trust Site" with the national hub.'),
    ),
  );

  return $items;
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_wt_site_setup($site_name) {
  $localWelshSplitStatus = \Drupal::config('config_split.config_split.local_welsh_site_split')->get('status');
  $localSplitStatus = \Drupal::config('config_split.config_split.local_site_split')->get('status');
  $nationalSplitStatus = \Drupal::config('config_split.config_split.national')->get('status');

  if (($localWelshSplitStatus || $localSplitStatus || $nationalSplitStatus) == FALSE) {
    drush_print("Couldn't detect the type of site. Please check that the relevant config split(s) are enabled in settings.php.");
    exit();
  }

  $type = null;

  if ($localWelshSplitStatus) {
    $type = 'welsh';
  }

  if ($localSplitStatus) {
    $type = 'local';
  }

  if ($nationalSplitStatus) {
    $type = 'national';
  }

  $site_email = drush_get_option('site_email');
  $admin_user_email = drush_get_option('admin_user_email');
  $strapline = drush_get_option('strapline');
  $hub_url = drush_get_option('hub_url');

  if (!$site_email || !$admin_user_email) {
    drush_print("Please specify the --site_email and --admin_user_email parameters.");
    exit();
  }

  if ($type !== 'national' && (!$hub_url)) {
    drush_print("Please specify the --hub_url parameter.");
    exit();
  }

  // Set the site name
  drush_invoke_process('@self', 'cset', ['system.site', 'name', $site_name]);

  // Set the site email
  drush_invoke_process('@self', 'cset', ['system.site', 'mail', $site_email]);

  // Set the admin user email
  $adminUser = \Drupal::entityTypeManager()->getStorage('user')->load(1);
  $adminUser->setEmail($admin_user_email);
  $adminUser->save();

  if ($type !== 'national') {
    // Set the hub URL and other details
    \Drupal::getContainer()->get('config.factory')->getEditable('wildlife_sharing.settings')->set('domains', [ [ 'title' => 'National (Hub)', 'url' => $hub_url ] ])->save();
  }

  // Set the strapline, or fall back to the site name
  if (!$strapline) {
    $strapline = $site_name;
  }

  drush_invoke_process('@self', 'cset', ['wildlife_trust.settings', 'strapline.text', $strapline]);

  // Optionally set Typekit ID
  $typekit_id = drush_get_option('typekit_id');
  if ($typekit_id) {
    drush_invoke_process('@self', 'cset', ['wildlife_typekit.settings', 'id', $typekit_id]);
  }

  // Optionally set Google API key
  $google_api = drush_get_option('google_api');
  if ($google_api) {
    drush_invoke_process('@self', 'cset', ['wildlife_google_api.settings', 'api_key', $google_api]);
  }

  // Import configuration
  drush_invoke_process('@self', 'csim');

  // Set up initial content
  drush_invoke_process('@self', 'en', 'wildlife_content');

  if ($type == 'national') {
    // Default content silhouettes.
    drush_invoke_process('@self', 'mi', [], ['group' => 'wildlife_default']);
  } else {
    // @todo Default content 5? basic pages.
    // drush_invoke_process('@self', 'mi', [], ['group' => 'wildlife_default_local']);
  }

  // Turn off wildlife_content module.
  drush_invoke_process('@self', 'pm-uninstall', 'wildlife_content');
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_wt_register_local($site_name) {
  $nationalSplitStatus = \Drupal::config('config_split.config_split.national')->get('status');

  if (!$nationalSplitStatus) {
    drush_print("This command should only be run on the national (hub) site, to register a newly provisioned local site.");
    exit();
  }

  $url = drush_get_option('url');

  if (!$url) {
    drush_print("Please specify the --url parameter.");
    exit();
  }

  // Get existing domains array
  $domainConfig = \Drupal::config('wildlife_sharing.settings')->get('domains');

  if (!is_array($domainConfig)) {
    $domainConfig = [];
  }

  // Check the specified site doesn't already exist
  $matches = array_filter($domainConfig, function($d) use ($site_name, $url) {
    if (!is_array($d) || !array_key_exists('title', $d) || !array_key_exists('url', $d)) {
      return false;
    }

    return (($d['title'] == $site_name) || ($d['url'] == $url));
  });

  if (count($matches) > 0) {
    drush_print("A site with the name '{$site_name}' and/or URL '{$url}' is already registered.");
    exit();
  }

  // Add the new entry to the array and save the configuration value
  $domainConfig[] = [
    'title' => $site_name,
    'url' => $url
  ];

  // Set the hub URL and other details
  \Drupal::getContainer()->get('config.factory')->getEditable('wildlife_sharing.settings')->set('domains', $domainConfig)->save();

  drush_print("Local site '{$site_name}' registered.");
}
