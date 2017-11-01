<?php

namespace Drupal\wildlife_scheduling;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Defines a scheduler manager.
 *
 * This is based on the SchedulerManager from the scheduling module but much
 * simpler and tailored to the specific needs of Wildlife Trust.
 */
class WildlifeScheduler {

  /**
   * Date formatter service object.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Scheduler Logger service object.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Entity Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityTypeManager;

  /**
   * Link Generator service object.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Constructs a SchedulerManager object.
   */
  public function __construct(DateFormatter $dateFormatter, LoggerInterface $logger, EntityTypeManager $entityTypeManager, LinkGeneratorInterface $linkGenerator) {
    $this->dateFormatter = $dateFormatter;
    $this->logger = $logger;
    $this->entityTypeManager = $entityTypeManager;
    $this->linkGenerator = $linkGenerator;
  }

  /**
   * Publish scheduled nodes.
   *
   * @return bool
   *   TRUE if any node has been published, FALSE otherwise.
   */
  public function publish() {
    $result = FALSE;

    // Select all nodes of the types that are enabled for scheduled publishing
    // and where publish_on is less than or equal to the current time.
    $nids = [];
    $scheduler_enabled_types = [
      'blog' => 'field_blog_publication_date',
      'news' => 'field_news_publication_date',
    ];
    foreach ($scheduler_enabled_types as $type => $field_name) {
      $request_date = new \DateTime();
      $request_date->setTimestamp(REQUEST_TIME);
      // Set the time to 23:59:59 so that any set to today will be found.
      $request_date->setTime(23, 59, 59);
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->exists($field_name)
        ->condition($field_name, $request_date->format(DATETIME_DATETIME_STORAGE_FORMAT), '<=')
        ->condition('type', $type, 'IN')
        ->condition('status', \Drupal\node\NodeInterface::NOT_PUBLISHED)
        ->sort($field_name)
        ->sort('nid');
      // Disable access checks for this query.
      // @see https://www.drupal.org/node/2700209
      $query->accessCheck(FALSE);
      $nids = array_merge($nids, $query->execute());
    }

    // In 8.x the entity translations are all associated with one node id
    // unlike 7.x where each translation was a separate node. This means that
    // the list of node ids returned above may have some translations that need
    // processing now and others that do not.
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    foreach ($nodes as $node_multilingual) {
      $languages = $node_multilingual->getTranslationLanguages();
      foreach ($languages as $language) {
        // The object returned by getTranslation() behaves the same as a $node.
        $node = $node_multilingual->getTranslation($language->getId());

        // If the current translation does not have a publish on value, or it is
        // later than the date we are processing then move on to the next.
        $field_name = $scheduler_enabled_types[$node->getType()];
        $publish_on = $node->{$field_name}->value;
        if (empty($publish_on) || $publish_on > REQUEST_TIME) {
          continue;
        }

        $is_moderatable_entity = \Drupal::service('workbench_moderation.moderation_information')->isModeratableEntity($node);
        $ready_to_be_published = $is_moderatable_entity ? $node->get('moderation_state')->getString() == 'ready_to_be_published' : TRUE;

        if ($ready_to_be_published) {
          // Update moderatable state if applicable.
          if ($is_moderatable_entity) {
            $new_state = 'published';
            $node->moderation_state->target_id = $new_state;
          }
          else {
            $node->set('changed', $publish_on);
            $node->setNewRevision();

            // Use the actions system to publish the node.
            $this->entityTypeManager->getStorage('action')
              ->load('node_publish_action')
              ->getPlugin()
              ->execute($node);
          }

          $old_creation_date = $node->getCreatedTime();

          // Use a core date format to guarantee a time is included.
          // @TODO: 't' calls should be avoided in classes.
          // Replace with dependency injection?
          $node->revision_log = t('Node published by Wildlife Scheduler on @now. Previous creation date was @date.', [
            '@now' => $this->dateFormatter->format(REQUEST_TIME, 'short'),
            '@date' => $this->dateFormatter->format($old_creation_date, 'short'),
          ]);

          // Log the fact that a scheduled publication is about to take place.
          $view_link = $node->link(t('View node'));
          $nodetype_url = Url::fromRoute('entity.node_type.edit_form', ['node_type' => $node->getType()]);
          $nodetype_link = $this->linkGenerator->generate(node_get_type_label($node) . ' ' . t('settings'), $nodetype_url);
          $logger_variables = [
            '@type' => node_get_type_label($node),
            '%title' => $node->getTitle(),
            'link' => $nodetype_link . ' ' . $view_link,
          ];

          $node->save();
          $this->logger->notice('@type: scheduled publishing of %title.', $logger_variables);
          $result = TRUE;
        }
      }
    }

    return $result;
  }

  /**
   * Unpublish scheduled nodes.
   *
   * @return bool
   *   TRUE if any node has been unpublished, FALSE otherwise.
   */
  public function unpublish() {
    $result = FALSE;

    // Select all nodes of the types that are enabled for scheduled unpublishing
    // and where unpublish_on is less than or equal to the current time.
    $nids = [];
    $scheduler_enabled_types = [
      'job' => 'field_job_closing_date',
      'event' => 'field_event_date.end_value',
    ];
    foreach ($scheduler_enabled_types as $type => $field_name) {
      $request_date = new \DateTime();
      $request_date->setTimestamp(REQUEST_TIME);
      $request_date->setTime(0, 0, 0);
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->exists($field_name)
        ->condition($field_name, $request_date->format(DATETIME_DATETIME_STORAGE_FORMAT), '<=')
        ->condition('type', $type, 'IN')
        ->condition('status', \Drupal\node\NodeInterface::PUBLISHED)
        ->sort($field_name)
        ->sort('nid');
      // Disable access checks for this query.
      // @see https://www.drupal.org/node/2700209
      $query->accessCheck(FALSE);
      $nids = array_merge($nids, $query->execute());
    }

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    /** @var NodeInterface $node_multilingual */
    foreach ($nodes as $node_multilingual) {
      $languages = $node_multilingual->getTranslationLanguages();
      foreach ($languages as $language) {
        // The object returned by getTranslation() behaves the same as a $node.
        $node = $node_multilingual->getTranslation($language->getId());

        // If the current translation does not have an unpublish on value, or it
        // is later than the date we are processing then move on to the next.
        $field_name = $scheduler_enabled_types[$node->getType()];
        if (strpos($field_name, '.')) {
          list($field_key, $value_key) = explode('.', $field_name);
        }
        else {
          $field_key = $field_name;
          $value_key = 'value';
        }
        $unpublish_on = date_create_from_format(DATETIME_DATE_STORAGE_FORMAT, $node->{$field_key}->{$value_key});
        if (empty($unpublish_on) || $unpublish_on->getTimestamp() > REQUEST_TIME) {
          continue;
        }

        $is_moderatable_entity = \Drupal::service('workbench_moderation.moderation_information')->isModeratableEntity($node);
        $ready_to_be_unpublished = $is_moderatable_entity ? $node->get('moderation_state')->getString() == 'published' : TRUE;

        if ($ready_to_be_unpublished) {
          // Update moderatable state if applicable.
          if ($is_moderatable_entity) {
            $new_state = 'archived';
            $node->moderation_state->target_id = $new_state;
          }
          else {
            $node->set('changed', $unpublish_on->getTimestamp());
            $node->setNewRevision();

            // Use the actions system to publish the node.
            $this->entityTypeManager->getStorage('action')
              ->load('node_unpublish_action')
              ->getPlugin()
              ->execute($node);
          }

          $old_change_date = $node->getChangedTime();

          // Use a core date format to guarantee a time is included.
          // @TODO: 't' calls should be avoided in classes.
          // Replace with dependency injection?
          $node->revision_log = t('Node unpublished by Scheduler on @now. Previous change date was @date.', [
            '@now' => $this->dateFormatter->format(REQUEST_TIME, 'short'),
            '@date' => $this->dateFormatter->format($old_change_date, 'short'),
          ]);

          // Log the fact that a scheduled unpublication is about to take place.
          $view_link = $node->link(t('View node'));
          $nodetype_url = Url::fromRoute('entity.node_type.edit_form', ['node_type' => $node->getType()]);
          $nodetype_link = $this->linkGenerator->generate(node_get_type_label($node) . ' ' . t('settings'), $nodetype_url);
          $logger_variables = [
            '@type' => node_get_type_label($node),
            '%title' => $node->getTitle(),
            'link' => $nodetype_link . ' ' . $view_link,
          ];
          $this->logger->notice('@type: scheduled unpublishing of %title.', $logger_variables);

          $node->save();
          $result = TRUE;
        }
      }
    }

    return $result;
  }

  /**
   * Run the lightweight cron.
   *
   * The Scheduler part of the processing performed here is the same as in the
   * normal Drupal cron run. The difference is that only scheduler_cron() is
   * executed, no other modules hook_cron() functions are called.
   *
   * This function is called from the external crontab job via url
   * /scheduler/cron/{access key} or it can be run interactively from the
   * Scheduler configuration page at /admin/config/content/scheduler/cron.
   */
  public function runLightweightCron() {
    $this->logger->notice('Lightweight cron run activated.');
//    scheduler_cron();
    if (ob_get_level() > 0) {
      $handlers = ob_list_handlers();
      if (isset($handlers[0]) && $handlers[0] == 'default output handler') {
        ob_clean();
      }
    }
    $this->logger->notice('Lightweight cron run completed.', ['link' =>$this->linkGenerator->generate(t('settings'), Url::fromRoute('scheduler.cron_form'))]);
  }
}
