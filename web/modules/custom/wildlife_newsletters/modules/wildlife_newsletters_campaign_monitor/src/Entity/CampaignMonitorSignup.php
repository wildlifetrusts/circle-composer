<?php

namespace Drupal\wildlife_newsletters_campaign_monitor\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the CampaignMonitorSignup entity.
 *
 * @ingroup campaign_monitor_signup
 *
 * @ConfigEntityType(
 *   id = "campaign_monitor_signup",
 *   label = @Translation("Campaign Monitor Signup Form"),
 *   fieldable = FALSE,
 *   handlers = {
 *     "list_builder" = "Drupal\wildlife_newsletters_campaign_monitor\Controller\CampaignMonitorSignupListBuilder",
 *     "form" = {
 *       "add" = "Drupal\wildlife_newsletters_campaign_monitor\Form\CampaignMonitorSignupForm",
 *       "edit" = "Drupal\wildlife_newsletters_campaign_monitor\Form\CampaignMonitorSignupForm",
 *       "delete" = "Drupal\wildlife_newsletters_campaign_monitor\Form\CampaignMonitorSignupDeleteForm"
 *     }
 *   },
 *   config_prefix = "campaign_monitor_signup",
 *   admin_permission = "administer campaign monitor signup entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "form" = "form",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/services/campaign_monitor/{campaign_monitor_signup}",
 *     "delete-form" = "/admin/config/services/campaign_monitor/{campaign_monitor_signup}/delete"
 *   }
 * )
 */
class CampaignMonitorSignup extends ConfigEntityBase implements ConfigEntityInterface {

  /**
   * The Signup ID.
   *
   * @var int
   */
  public $id;

  /**
   * The Signup Form Machine Name.
   *
   * @var string
   */
  public $name;

  /**
   * The Signup Form Title.
   *
   * @var string
   */
  public $title;

  /**
   * The Signup Form Status.
   *
   * @var boolean
   */
  public $status;

  /**
   * The Signup Form.
   *
   * @var array
   */
  public $form;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->title;
  }

}
