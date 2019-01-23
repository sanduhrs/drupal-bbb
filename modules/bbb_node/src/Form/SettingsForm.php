<?php

namespace Drupal\bbb_node\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an administration settings form.
 */
class SettingsForm extends ConfigFormBase {

  const DISPLAY_MODE_INLINE = 'inline';
  const DISPLAY_MODE_BLANK = 'blank';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bbb_node.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'bbb_node_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get all settings.
    $config = $this->configFactory->get('bbb_node.settings');
    $settings = $config->get();

    $form['bbb_client'] = [
      '#title' => $this->t('Client settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];

    $form['bbb_client']['display_mode'] = [
      '#title' => $this->t('Block Links'),
      '#type' => 'radios',
      '#options' => [
        self::DISPLAY_MODE_INLINE => $this->t('Display inline'),
        self::DISPLAY_MODE_BLANK => $this->t('Open in a new window'),
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get config factory.
    $config = $this->configFactory->getEditable('bbb_node.settings');

    $form_values = $form_state->getValues();

    $config
      ->set('display_mode', $form_values['bbb_client']['display_mode'])
      ->set('display_height', $form_values['bbb_client']['display_height'])
      ->set('display_width', $form_values['bbb_client']['display_width'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
