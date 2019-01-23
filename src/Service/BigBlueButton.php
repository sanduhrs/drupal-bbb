<?php

namespace Drupal\bbb\Service;

use BigBlueButton\BigBlueButton as BigBlueButtonBase;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class BigBlueButton.
 *
 * Drupal wrapper for BigBlueButton\BigBlueButton,
 *
 * @package Drupal\bbb\Service
 */
class BigBlueButton extends BigBlueButtonBase {

  /**
   * BigBlueButton constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    if (!getenv('BBB_SECURITY_SALT') && !getenv('BBB_SECRET')) {
      putenv('BBB_SECRET=' . $config_factory->get('bbb.settings')->get('security_salt'));
    }
    if (!getenv('BBB_SERVER_BASE_URL')) {
      putenv('BBB_SERVER_BASE_URL=' . $config_factory->get('bbb.settings')->get('base_url'));
    }
    parent::__construct();
  }

}
