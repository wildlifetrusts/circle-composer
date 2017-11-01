<?php

namespace Drupal\wildlife_development\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Development module.
 */
class StyleguidePagesController extends ControllerBase {
  /**
   * Returns a the Header demo page.
   */
  public function headers() {
    $headers = $this->rawHeaderData;

    $build['wildlife_development_headers'] = array(
      '#theme' => 'wildlife_development_headers',
      '#headers' => $headers,
    );

    return $build;
  }

  /**
   * Returns a the Cards demo page.
   */
  public function cards() {
    $build['wildlife_development_cards'] = array(
      '#theme' => 'wildlife_development_cards',
    );

    return $build;
  }

  /**
   * Returns a the Teasers demo page.
   */
  public function teasers() {
    $build['wildlife_development_teasers'] = array(
      '#theme' => 'wildlife_development_teasers',
    );

    return $build;
  }

  /**
   * Returns a the Spotlights demo page.
   */
  public function spotlights() {
    $build['wildlife_development_spotlights'] = array(
      '#theme' => 'wildlife_development_spotlights',
    );

    return $build;
  }

  /**
   * An array of the data for the headers.
   *
   * @var array
   */
  protected $rawHeaderData = array(
    'alderney' => array(
      'name' => 'Alderney Wildlife Trust',
      'logo' => 'jpg',
    ),
    'avon' => array(
      'name' => 'Avon Wildlife Trust',
    ),
    'berks' => array(
      'name' => 'Berks, Bucks & Oxon Wildlife Trust',
    ),
    'birmingham' => array(
      'name' => 'Birmingham & Black Country',
    ),
    'brecknock' => array(
      'name' => 'Brecknock | Brycheiniog',
      'logo' => 'jpg',
    ),
    'cheshire' => array(
      'name' => 'Cheshire Wildlife Trust',
    ),
    'cornwall' => array(
      'name' => 'Cornwall Wildlife Trust',
    ),
    'cumbria' => array(
      'name' => 'Cumbria Wildlife Trust',
    ),
    'derbyshire' => array(
      'name' => 'Derbyshire Wildlife Trust',
    ),
    'devon' => array(
      'name' => 'Devon Wildlife Trust',
    ),
    'dorset' => array(
      'name' => 'Dorset Wildlife Trust',
      'logo' => 'png',
    ),
    'durham' => array(
      'name' => 'Durham Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'png',
    ),
    'essex' => array(
      'name' => 'Essex Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'png',
    ),
    'gloucestershire' => array(
      'name' => 'Gloucestershire Wildlife Trust',
      'logo' => 'png',
    ),
    'gwent' => array(
      'name' => 'Gwent Wildlife Trust',
      'logo' => 'JPG',
    ),
    'hampshire' => array(
      'name' => 'Hampshire & Isle of Wight Wildlife Trust',
    ),
    'herefordshire' => array(
      'name' => 'Herefordshire Wildlife Trust',
    ),
    'herts' => array(
      'name' => 'Herts and Middlesex Wildlife Trust',
    ),
    'scilly' => array(
      'name' => 'Isles of Scilly Wildlife Trust',
      'logo' => 'jpg',
    ),
    'kent' => array(
      'name' => 'Kent Wildlife Trust',
      'logo' => 'png',
    ),
    'leicestershire' => array(
      'name' => 'Leicestershire and Rutland Wildlife Trust',
    ),
    'lincolnshire' => array(
      'name' => 'Lincolnshire Wildlife Trust',
      'logo' => 'jpg',
    ),
    'london' => array(
      'name' => 'London Wildlife Trust',
      'logo' => 'png',
    ),
    'manx' => array(
      'name' => 'Manx Wildlife Trust',
      'logo' => 'jpg',
    ),
    'montgomeryshire' => array(
      'name' => 'Montgomeryshire Wildlife Trust',
      'logo' => 'png',
    ),
    'norfolk' => array(
      'name' => 'Norfolk Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'jpg',
    ),
    'northwales' => array(
      'name' => 'North Wales Wildlife Trust',
      'logo' => 'jpg',
    ),
    'northumberland' => array(
      'name' => 'Northumberland Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'png',
    ),
    'nottinghamshire' => array(
      'name' => 'Nottinghamshire Wildlife Trust',
    ),
    'radnorshire' => array(
      'name' => 'Radnorshire Wildlife Trust',
      'logo' => 'jpg',
    ),
    'sheffield' => array(
      'name' => 'Sheffield and Rotherham Wildlife Trust',
      'logo' => 'png',
    ),
    'shropshire' => array(
      'name' => 'Shropshire Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'jpg',
    ),
    'somerset' => array(
      'name' => 'Somerset Wildlife Trust',
      'logo' => 'jpg',
    ),
    'staffordshire' => array(
      'name' => 'Staffordshire Wildlife Trust',
    ),
    'suffolk' => array(
      'name' => 'Suffolk Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'png',
    ),
    'surrey' => array(
      'name' => 'Surrey Wildlife Trust',
      'logo' => 'png',
    ),
    'sussex' => array(
      'name' => 'Sussex Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'svg',
    ),
    'teesvalley' => array(
      'name' => 'Tees Valley Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'png',
    ),
    'bedfordshire' => array(
      'name' => 'Wildlife Trust for Bedfordshire, Cambridgeshire and Northamptonshire',
    ),
    'lancashire' => array(
      'name' => 'The Wildlife Trust for Lancashire, Manchester and North Merseyside',
      'logo' => 'png',
    ),
    'southwales' => array(
      'name' => 'The Wildlife Trust of South & West Wales',
      'logo' => 'jpg',
    ),
    'ulster' => array(
      'name' => 'Ulster Wildlife',
      'hide_name' => TRUE,
      'logo' => 'jpg',
    ),
    'warwickshire' => array(
      'name' => 'Warwickshire Wildlife Trust',
      'logo' => 'png',
    ),
    'wiltshire' => array(
      'name' => 'Wiltshire Wildlife Trust',
      'hide_name' => TRUE,
      'logo' => 'jpeg',
    ),
    'worcestershire' => array(
      'name' => 'Worcestershire Wildlife Trust',
    ),
    'yorkshire' => array(
      'name' => 'Yorkshire Wildlife Trust',
    ),
  );
}
