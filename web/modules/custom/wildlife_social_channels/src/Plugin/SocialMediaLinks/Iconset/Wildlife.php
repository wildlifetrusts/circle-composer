<?php

namespace Drupal\wildlife_social_channels\Plugin\SocialMediaLinks\Iconset;

use Drupal\social_media_links\IconsetBase;
use Drupal\social_media_links\IconsetInterface;

/**
 * Provides 'wildlife' iconset.
 *
 * @Iconset(
 *   id = "wildlife",
 *   publisher = "The Wildlife Trusts",
 *   name = "Wildlife Trust",
 * )
 */
class Wildlife extends IconsetBase implements IconsetInterface {
  /**
   * {@inheritdoc}
   */
  public function getStyle() {
    return [
      '24' => '24x24',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIconElement($platform, $style) {
    $icon_name = $platform->getIconName();
    $channel_name = $platform->getName();

    $icon = [
      '#theme' => 'social_icon',
      '#icon_name' => $icon_name,
      '#channel_name' => $channel_name,
    ];

    return $icon;
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($iconset_id) {
    $this->path = $this->finder->getPath($iconset_id) ? $this->finder->getPath($iconset_id) : 'library';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibrary() {
    return [
      'wildlife_social_channels/icons',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIconPath($icon_name, $style) {
    return NULL;
  }
}
