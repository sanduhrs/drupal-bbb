<?php

namespace Drupal\bbb_node\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides an administration settings form.
 */
class BBBNodeTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to remove the BigBlueButton settings for %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.bbb_node_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Are you sure? @default', ['@default' => parent::getDescription()]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->entity->delete();
    $this->messenger()->addStatus($this->t('Category %label has been deleted.', array('%label' => $this->entity->label())));
    $form_state->setRedirect('entity.bbb_node_type.collection');
  }

}
