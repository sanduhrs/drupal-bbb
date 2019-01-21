<?php

namespace Drupal\bbb\Service;

use BigBlueButton\BigBlueButton as BigBlueButtonBase;

class BigBlueButton extends BigBlueButtonBase {

  public function __construct() {
    if (!getenv('BBB_SECURITY_SALT') && !getenv('BBB_SECRET')) {
      putenv('BBB_SECRET=' . \Drupal::config('bbb.settings')->get('security_salt'));
    }
    if (!getenv('BBB_SERVER_BASE_URL')) {
      putenv('BBB_SERVER_BASE_URL=' . \Drupal::config('bbb.settings')->get('base_url'));
    }
    parent::__construct();
  }

}
