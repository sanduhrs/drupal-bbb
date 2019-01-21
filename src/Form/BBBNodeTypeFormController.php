<?php

namespace Drupal\bbb\Form;

use Drupal\bbb\Service\NodeMeeting;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an administration settings form.
 */
class BBBNodeTypeFormController extends EntityForm {

  /**
   * Node based Meeting API.
   *
   * @var \Drupal\bbb\Service\NodeMeeting
   */
  protected $nodeMeeting;

  /**
   * BBBNodeTypeFormController constructor.
   *
   * @param \Drupal\bbb\Service\NodeMeeting $node_meeting
   *   Node based Meeting API.
   */
  public function __construct(NodeMeeting $node_meeting) {
    $this->nodeMeeting = $node_meeting;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('bbb.node_meeting'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'bbb_content_type';
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\bbb\Entity\BBBNodeTypeInterface $bbbNodeType */
    $bbbNodeType = $this->entity;

    if ($bbbNodeType->isNew()) {
      $names = $this->getNames();
      $options = [];
      foreach ($names as $type => $label) {
        if (!$this->nodeMeeting->isTypeOf($type)) {
          $options[$type] = $label;
        }
      }
      $form['node'] = [
        '#title' => $this->t('Available content types'),
        '#type' => 'fieldset',
        '#tree' => FALSE,
        '#collapsible' => FALSE,
        '#collapsed' => FALSE,
        '#weight' => 0,
      ];

      $form['node']['type'] = [
        '#title' => $this->t('Content types'),
        '#type' => 'select',
        '#options' => $options,
      ];
    }

    $form['bbb'] = [
      '#title' => $this->t('Big Blue Button settings'),
      '#type' => 'details',
      '#tree' => TRUE,
      '#open' => TRUE,
      '#group' => 'additional_settings',
      '#weight' => 1,
    ];

    $form['bbb']['active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Treat this node type as meeting'),
      '#default_value' => $bbbNodeType->active(),
      '#weight' => 0,
    ];

    $form['bbb']['showLinks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show links to attend, moderate or terminate a meeting beneath the node'),
      '#default_value' => $bbbNodeType->showLinks(),
      '#weight' => 1,
    ];

    $form['bbb']['showStatus'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display meeting status on node'),
      '#default_value' => $bbbNodeType->showStatus(),
      '#weight' => 2,
    ];

    $form['bbb']['moderatorRequired'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require a moderator present to run the meeting.'),
      '#default_value' => $bbbNodeType->moderatorRequired(),
      '#weight' => 3,
    ];

    $form['bbb']['welcome'] = [
      '#title' => $this->t('Welcome message'),
      '#type' => 'textfield',
      '#default_value' => $bbbNodeType->welcome(),
      '#maxlength' => 255,
      '#description' => $this->t('A welcome message that gets displayed on the chat window when the participant joins. You can include keywords (%%CONFNAME%%, %%DIALNUM%%, %%CONFNUM%%) which will be substituted automatically.'),
      '#weight' => 5,
    ];

    $form['bbb']['dialNumber'] = [
      '#title' => $this->t('Dial number'),
      '#type' => 'textfield',
      '#default_value' => $bbbNodeType->dialNumber(),
      '#maxlength' => 32,
      '#description' => $this->t('The dial access number that participants can call in using regular phone.'),
      '#weight' => 6,
    ];

    $form['bbb']['moderatorPW'] = [
      '#title' => $this->t('Moderator password'),
      '#type' => 'textfield',
      '#default_value' => $bbbNodeType->moderatorPW(),
      '#maxlength' => 32,
      '#description' => $this->t('The password that will be required for moderators to join the meeting or for certain administrative actions (i.e. ending a meeting).'),
      '#weight' => 7,
    ];

    $form['bbb']['attendeePW'] = [
      '#title' => $this->t('Attendee password'),
      '#type' => 'textfield',
      '#default_value' => $bbbNodeType->attendeePW(),
      '#maxlength' => 32,
      '#description' => $this->t('The password that will be required for attendees to join the meeting.'),
      '#weight' => 8,
    ];

    $form['bbb']['logoutURL'] = [
      '#title' => $this->t('Logout URL'),
      '#type' => 'textfield',
      '#default_value' => $bbbNodeType->logoutURL(),
      '#maxlength' => 255,
      '#description' => $this->t('The URL that the Big Blue Button client will go to after users click the OK button on the <em>You have been logged out message</em>.'),
      '#weight' => 9,
    ];

    if ($this->currentUser()->hasPermission('record meetings')) {
      $form['bbb']['record'] = [
        '#title' => $this->t('Record new meetings of this type, by default.'),
        '#type' => 'checkbox',
        '#default_value' => $bbbNodeType->record(),
        '#description' => 'Meetings that are recorded can be viewed at <strong>http://example.com/playback/slides/playback.html?meetingId=<meetingId></strong> (The meeting ID is about 54 characters long.)',
        '#weight' => 4,
      ];
    }

    $form['#submit'][] = [$this, 'saveEntity'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
//    $this->saveEntity($form, $form_state);
  }

  public function saveEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\bbb\Entity\BBBNodeTypeInterface $bbbNodeType */
    $bbbNodeType = $this->entity;
    $id = $form_state->getValue('type', FALSE);
    $values = $form_state->getValue('bbb');
    // If there are some values that are not empty.
    if (count(array_filter($values)) || !$bbbNodeType->isNew()) {
      $bbbNodeType->setActive($values['active']);
      $bbbNodeType->setShowLinks($values['showLinks']);
      $bbbNodeType->setShowStatus($values['showStatus']);
      $bbbNodeType->setModeratorRequired($values['moderatorRequired']);
      $bbbNodeType->setWelcome($values['welcome']);
      $bbbNodeType->setDialNumber($values['dialNumber']);
      $bbbNodeType->setModeratorPW($values['moderatorPW']);
      $bbbNodeType->setAttendeePW($values['attendeePW']);
      $bbbNodeType->setLogoutURL($values['logoutURL']);
      $bbbNodeType->setRecord($values['record']);
      if (!empty($id)) {
        $names = $this->getNames();
        $label = $names[$id];
        $result = $bbbNodeType->setId($id);
        if ($result) {
          $bbbNodeType->setLabel($label);
          $form_state->setRedirect('entity.bbb_node_type.collection');
          $this->messenger()->addStatus('BigBlueButton settings saved.');
        }
      }
      $this->entity->save();
    }
  }

  /**
   * Get node type list of labels.
   *
   * @return array
   *   ID and labels asociative array of available node types.
   */
  protected function getNames() {
    return array_map(function (NodeTypeInterface $bundle_info) {
      return $bundle_info->label();
    }, $this->entityTypeManager->getStorage('node_type')->loadMultiple());
  }

}
