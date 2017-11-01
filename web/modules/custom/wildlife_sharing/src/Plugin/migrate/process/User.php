<?php
/**
 * @file
 * Contains \Drupal\wildlife_sharing\Plugin\migrate\process\User.
 */

namespace Drupal\wildlife_sharing\Plugin\migrate\process;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Lookup existing Term
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_sharing_user"
 * )
 */
class User extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $active_url = $row->getSourceProperty('active_url');
    $email = $active_url['email'];
    $name = $active_url['title'];
    if (empty($email)) {
      return 0;
    }

    // Attempt to lookup user.
    $user = user_load_by_mail($email);
    if ($user) {
      return $user->id();
    }
    // Attempt to create user.
    $user = \Drupal\user\Entity\User::create();

    $user->setPassword(user_password());
    $user->enforceIsNew();
    $user->setEmail($email);
    $user->setUsername($name);
    $user->addRole('trust');
    $user->activate();

    // Save user account.
    try {
      $user->save();
    }
    catch (EntityStorageException $e) {
      // A user couldn't be saved so give up.
      return 0;
    }
    return $user->id();
  }
}

