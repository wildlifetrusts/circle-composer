<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = [
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config',
];

/**
 * Wildlife Trusts Config Split setup.
 *
 * SUMMARY:
 * The 'dev' split should always be disabled in production environments.
 * National site - enable 'national' only.
 * Local sites - enable 'local_site_split' only.
 * Local sites with Welsh language - enable 'local_site_split' and 'local_welsh_site_split'.
 *
 * Split details:
 *   - Dev split (dev): Turns on development modules and registers example
 *       content migrations.
 *
 *   - Ignore split (ignore): Simply stores config_ignore.settings.yml which
 *       prevents certain settings being overridden when config is imported.
 *       This is weighted more heavily than the other splits as it needs to be
 *       set last. If it is not in its own split, it prevents some of the
 *       initial config from being set. This is enabled by default.
 *
 *   - Local site split (local_site_split): Adds 'Local' site specific config
 *       and entities including Local Customisation and views differences etc.
 *
 *   - Local Welsh site split (local_welsh_site_split): Turns on the permissions
 *       language switcher block for local sites needing Welsh translations.
 *
 *   - National split (national): Adds National site specific config, entities
 *       and permissions such as Habitat and Species permissions and Trust view
 *       and nodes.
 */
//$config['config_split.config_split.local_site_split']['status'] = TRUE;
//$config['config_split.config_split.local_welsh_site_split']['status'] = TRUE;
//$config['config_split.config_split.national']['status'] = TRUE;
$config['config_split.config_split.dev']['status'] = false;

$config['search_api.server.keyword_multilingual'] = [
  'backend_config' => [
    'connector_config' => [
      'host' => 'hostname',
      'path' => '/solr',
      'core' => 'keyword_CORENAME',
      'port' => '8983',
    ],
  ],
];

$config['search_api.server.location_multilingual'] = [
  'backend_config' => [
    'connector_config' => [
      'host' => 'hostname',
      'path' => '/solr',
      'core' => 'location',
      'port' => '8983',
    ],
  ],
];

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 *
 * See: tests/installer-features/installer.feature
 */
$settings['install_profile'] = 'standard';
