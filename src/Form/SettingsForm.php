<?php

namespace Drupal\bbb\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an administration settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bbb.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'bbb_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get all settings.
    $config = $this->configFactory->get('bbb.settings');
    $settings = $config->get();

    $form['bbb_server'] = [
      '#title' => 'Server settings',
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#description' => $this->t('Run <em>bbb-conf --secret</em> at the server to get correct values. Read more about BigBlueButton on <a href="@home">@home_title</a>. See the documentation for <a href="@documentation">@documentation_title</a>', [
        '@home' => 'http://bigbluebutton.org/',
        '@home_title' => $this->t('BigBlueButton.org'),
        '@documentation' => 'http://code.google.com/p/bigbluebutton/',
        '@documentation_title' => $this->t('installation instructions'),
      ]),
      '#attributes' => [
        'id' => 'modal-command-area',
      ],
    ];

    $form['bbb_server']['base_url'] = [
      '#title' => $this->t('Base URL'),
      '#type' => 'textfield',
      '#default_value' => $settings['base_url'],
    ];

    $form['bbb_server']['security_salt'] = [
      '#title' => $this->t('Security Salt'),
      '#type' => 'textfield',
      '#default_value' => $settings['security_salt'],
      '#description' => $this->t('The predefined security salt. This is a server side configuration option. Please check the BigBlueButton <a href="@documentation">@documentation_title</a>.', [
        '@documentation' => 'http://code.google.com/p/bigbluebutton/',
        '@documentation_title' => $this->t('installation instructions'),
      ]),
    ];

    $form['bbb_client'] = [
      '#title' => $this->t('Client settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#attached' => [
        'library' => ['core/drupal.dialog.ajax', 'bbb/test'],
      ],
    ];

    $form['bbb_client']['display_mode'] = [
      '#title' => $this->t('Block Links'),
      '#type' => 'radios',
      '#options' => [
        'inline' => $this->t('Display inline'),
        'blank' => $this->t('Open in a new window'),
      ],
      '#default_value' => $settings['display_mode'],
      '#description' => $this->t('How to open links to meetings from the block provided by the Big Blue Button module.'),
    ];
    $form['bbb_client']['display_height'] = [
      '#title' => $this->t('Height x Width'),
      '#type' => 'textfield',
      '#default_value' => $settings['display_height'],
      '#prefix' => '<div class="container-inline">',
      '#suffix' => 'x',
      '#size' => 4,
    ];
    $form['bbb_client']['display_width'] = [
      '#type' => 'textfield',
      '#default_value' => $settings['display_width'],
      '#suffix' => '</div>',
      '#size' => 4,
      '#description' => '<br />' . $this->t('Give dimensions for inline display, e.g. <em>580px</em> x <em>100%</em>.'),
    ];

    $form['connection'] = [
      '#type' => 'button',
      '#executes_submit_callback' => FALSE,
      '#value' => $this->t('Test Connection'),
      '#attributes' => [
        'class' => ['use-ajax'],
      ],
      '#ajax' => [
        'callback' => '::testConnection',
        'wrapper' => 'modal-command-area',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get config factory.
    $config = $this->configFactory->getEditable('bbb.settings');

    $form_values = $form_state->getValues();

    $config
      ->set('security_salt', $form_values['bbb_server']['security_salt'])
      ->set('base_url', $form_values['bbb_server']['base_url'])
      ->set('display_mode', $form_values['bbb_client']['display_mode'])
      ->set('display_height', $form_values['bbb_client']['display_height'])
      ->set('display_width', $form_values['bbb_client']['display_width'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url_key = ['bbb_server', 'base_url'];
    $url = $form_state->getValue($url_key);
    if (empty($url)) {
      $form_state->setErrorByName(implode('][', $url_key), $this->t('Base url should not be empty.'));
    }
    else {
      if (strpos($url, 'http') !== 0) {
        $form_state->setErrorByName(implode('][', $url_key), $this->t('Base url protocol is not defined. Use one of <em>http://</em> or <em>https://</em>.'));
      }
      if (substr($url, -1) !== '/') {
        $form_state->setErrorByName(implode('][', $url_key), $this->t('Base url should end with <em>/</em> <em>(the slash symbol)</em>.'));
      }
      if (substr($url, -5) === '/api/') {
        $form_state->setErrorByName(implode('][', $url_key), $this->t('Do not set <em>api/</em> suffix.'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * Test connection to Big Blue Button.
   */
  public function testConnection($form, FormStateInterface $form_state) {
    $response = simplexml_load_file($form_state->getValue(['bbb_server', 'base_url']) . 'api/');
    if (!$response) {
      $content = $this->t('Bad connection');
    }
    else {
      $content = $this->t(
          "Good connection.<br />Status: <em>@status</em><br />Version: <em>@version</em>",
          ['@status' => $response->returncode, '@version' => $response->version]
        );
    }
    $commands = new AjaxResponse();
    $commands->addCommand(new OpenModalDialogCommand(
      $this->t('Test connection'),
      $content
    ));
    return $commands;
  }

}
