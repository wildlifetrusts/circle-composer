<?php

namespace Drupal\wildlife_newsletters_campaign_monitor\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the CampaignMonitorSignup entity edit form.
 *
 * @ingroup campaign_monitor_signup
 */
class CampaignMonitorSignupForm extends EntityForm {

  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $signup = $this->entity;

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#size' => 35,
      '#maxlength' => 32,
      '#default_value' => $signup->title,
      '#description' => $this->t('The title for this signup form.'),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $signup->id,
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'source' => array('title'),
        'exists' => 'wildlife_newsletters_campaign_monitor_signup_load_entity',
      ),
      '#description' => t('A unique machine-readable name for this list. It must only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$signup->isNew(),
    );

    $url = Url::fromUri('http://www.campaignmonitor.com')->setOption('target', '_blank');
    $link = Link::fromTextAndUrl(t('Campaign Monitor'), $url)->toString();

    $description = t('Paste the code for your Sign up form.<br>');
    $description .= t('To get the form code from @link:', ['@link' => $link]);
    $description .= '<ul>';
    $description .= t('<li>Navigate to <strong>Lists &amp; subscribers</strong>.</li>') ;
    $description .= t('<li>Go to the list you want to use.</li>');
    $description .= t('<li>Follow the <strong>Sign up forms</strong> link in the sidebar</li>');
    $description .= '</ul>';

    $form['form'] = array(
      '#type' => 'text_format',
      '#title' => 'Form',
      '#default_value' => isset($signup->form['value']) ? $signup->form['value'] : '',
      '#format' => isset($signup->form['format']) ? $signup->form['format'] : 'full_html',
      '#description' => $description,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $signup \Drupal\wildlife_newsletters_campaign_monitor\Entity\CampaignMonitorSignup */
    $signup = $this->getEntity();
    $signup->save();

    \Drupal::service('router.builder')->setRebuildNeeded();

    $form_state->setRedirect('campaign_monitor_signup.admin');
  }


  public function exist($id) {
    $entity = $this->entityQuery->get('campaign_monitor_signup')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }
}
