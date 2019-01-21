<?php

namespace Drupal\bbb\Form;

use Drupal\bbb\Service\NodeMeeting;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides an administration settings form.
 */
class EndMeetingConfirmForm extends ConfirmFormBase {

  /**
   * Node based Meeting api.
   *
   * @var \Drupal\bbb\Service\NodeMeeting
   */
  protected $nodeMeeting;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('bbb.node_meeting')
    );
  }

  public function __construct(NodeMeeting $node_meeting) {
    $this->nodeMeeting = $node_meeting;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'bbb_end_meeting_confirm_form';
  }

  /**
   * {@inheritdoc}
   * Terminate confirm form
   */
//  public function buildForm(array $form, FormStateInterface $form_state) {
//
//      t('Terminate'),
//      t('Cancel')
//    );
//
//    return $form;
//  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $node = $this->getRequest()->get('node');
    return $this->t('Are you sure you want to terminate the meeting %name?', [
      '%name' => $node->getTitle(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('This action cannot be undone, all attendees will be removed from the meeting.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $node = $this->getRequest()->get('node');
    return Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->getRequest()->get('node');
    $request = $this->nodeMeeting->end($node);

    if ($request === FALSE) {
      $this->messenger()->addError($this->t('There was an error terminating the meeting.'));
    }
    else {
      $this->messenger()->addStatus($this->t('The meeting has been terminated.'));
    }
    return new RedirectResponse(Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE]));
  }

}
