<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\UserMailLookup.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\user\Entity\User;

/**
 * Transform email address to user ID.
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_user_mail_lookup"
 * )
 */
class UserMailLookup extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    /** @var User $user */
    $user = user_load_by_mail($this->configuration['mail']);

    if (empty($user)) {
      return NULL;
    }

    return $user->id();
  }
}

