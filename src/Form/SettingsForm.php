<?php

namespace Drupal\bbb\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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

    $form['actions']['connection'] = [
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
      '#attached' => [
        'library' => ['core/drupal.dialog.ajax'],
      ],
      '#weight' => 30,
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
      $link = Url::fromUri(
        'https://mconf.github.io/api-mate/',
        [
          'fragment' => http_build_query([
            'server' => $form_state->getValue(['bbb_server', 'base_url']),
            'sharedSecret' => $form_state->getValue([
              'bbb_server',
              'security_salt',
            ]),
          ]),
        ])->toString();
      $content = $this->t(
        'Good connection.<br />Status: <em>@status</em><br />Version: <em>@version</em><br /><a href="@link" target="_blank">Link to the API-Mate</a>',
        [
          '@status' => $response->returncode,
          '@version' => $response->version,
          '@link' => $link,
        ]
      );
      $content .= '<br /><iframe src="' . $link . '" style="width: 100%; min-height: 50vh" frameborder="0"></iframe>';
    }
    $commands = new AjaxResponse();
    $commands->addCommand(new OpenModalDialogCommand(
      $this->t('Test connection'),
      $content,
      [
        'width' => "90%",
      ]
    ));
    return $commands;
  }

}
