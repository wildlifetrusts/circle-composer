<?php

namespace Drupal\wildlife_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class WildlifeAdminHelp extends ControllerBase {

  /**
   * Returns the help landing page.
   */
  public function main() {
    $account = $this->currentUser();

    $build = [
      '#type' => 'container',
    ];

    $content_items = [
      'spotlights' => [
        'title' => t('Spotlights'),
        'description' => 'Guidance for creating Spotlight components.',
        'url' => Url::fromRoute('wildlife_admin.spotlight_help_page'),
      ],
    ];

    $build['content'] = [
      '#theme' => 'admin_block',
      '#block' => [
        'title' => t('Content help'),
        'content' => [
          '#theme' => 'admin_block_content',
          '#content' => $content_items,
        ],
      ],
    ];

    if ($account->hasPermission('administer configuration', $account)) {
      $config_items = [
        'google_api_key' => [
          'title' => t('Google API Key'),
          'description' => 'Set up an API key in order to activate location search, static and interactive maps on the website.',
          'url' => Url::fromRoute('wildlife_google_api.config'),
        ],
        'disqus' => [
          'title' => t('Disqus comments'),
          'description' => 'Head to the Disqus settings page to enable comments on the site.',
          'url' => Url::fromRoute('disqus.settings'),
        ],
        'newsletters_mailchimp' => [
          'title' => t('Newsletters: Mailchimp'),
          'description' => 'Add a Mailchimp API key and set up Mailchimp sign-up forms for use in Newsletter sign-up components.',
          'url' => Url::fromRoute('mailchimp.admin'),
        ],
        'newsletters_campaign_monitor' => [
          'title' => t('Newsletters: Campaign Monitor'),
          'description' => 'Set up Campaign Monitor sign-up forms for use in Newsletter sign-up components.',
          'url' => Url::fromRoute('campaign_monitor_signup.admin'),
        ],
      ];

      $build['config'] = [
        '#theme' => 'admin_block',
        '#block' => [
          'title' => t('Configuration'),
          'content' => [
            '#theme' => 'admin_block_content',
            '#content' => $config_items,
          ],
        ],
      ];

      $theme_items = [
        'variation' => [
          'title' => t('Theme variation'),
          'description' => 'Set up theme variation configuration such as colour scheme, logos and strapline text to personalise the site.',
          'url' => Url::fromRoute('wildlife_theming.admin'),
        ],
        'favicons' => [
          'title' => t('Favicons'),
          'description' => 'Add responsive favicons which are used in the active tab in the browser as well as for touch icons on phones etc.',
          'url' => Url::fromRoute('responsive_favicons.admin'),
        ],
        'typekit' => [
          'title' => t('Typekit'),
          'description' => 'The fonts for the site are provided by Typekit. You can set this up on the Typekit Admin page.',
          'url' => Url::fromRoute('wildlife_typekit.admin'),
        ],
        'social' => [
          'title' => t('Social media channels'),
          'description' => 'Add and re-order the social media channels to appear in the header of the site.',
          'url' => Url::fromRoute('wildlife_social_channels.config'),
        ],
        'footer' => [
          'title' => t('Footer blocks'),
          'description' => 'Configure the footer blocks to appear on the site.',
          'url' => Url::fromRoute('wildlife_footer_blocks.footer_information_block_config'),
        ],
      ];

      $build['theme'] = [
        '#theme' => 'admin_block',
        '#block' => [
          'title' => t('Theme'),
          'content' => [
            '#theme' => 'admin_block_content',
            '#content' => $theme_items,
          ],
        ],
      ];
    }

    return $build;
  }

  /**
   * Returns the Spotlight help page.
   */
  public function spotlights() {
    return [
      '#theme' => 'spotlights_help',
    ];
  }
}
