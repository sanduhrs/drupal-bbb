<?php

namespace Drupal\bbb\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Class Theme.
 *
 * @package Drupal\bbb\Service
 */
class Theme {

  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Theme constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * Theme inline meeting
   *
   * @param $meeting
   *
   * @return array
   */
  public function meeting($meeting) {
    $url = Url::fromRoute('bbb.meeting.redirect', [
      'node' => $meeting['meeting'],
      'mode' => $meeting['mode'],
    ], ['absolute' => TRUE])->toString();
    return [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'src' => $url,
        'style' => [
          'height' => $meeting['height'],
          'width' => $meeting['width'],
          'border' => 0,
        ],
      ],
    ];
  }

  /**
   * Theme meeting status.
   *
   * @param $meeting
   *
   * @return array
   */
  public function meetingStatus($meeting) {
    $meeting = $meeting['meeting'];
    $running = isset($meeting->running) && $meeting->running;
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          $running ? 'bbb-status-is-running' : 'bbb-status-is-not-running',
        ],
      ],
      'message' => $this->t('Status: Meeting is @status.', ['@status' => $running ? $this->t('in progress') : $this->t('not running')]),
    ];
  }

  /**
   * Theme meeting status.
   *
   * @param $meeting
   *
   * @return array|string
   */
  public function meetingRecord($meeting) {
    $meeting = $meeting['meeting'];
    // Only if the meeting is set to record do we output as such.
    $output = '';
    if (isset($meeting->record) && $meeting->record) {
      $output = [
        '#type' => 'container',
        '#attributes' => ['class' => ['bbb-meeting-record']],
        'message' => $this->t('This meeting is set to be recorded.'),
      ];
    }
    return $output;
  }

  /**
   * Theme meeting details block.
   *
   * @param $meeting
   *
   * @return array
   */
  public function blockMeeting($meeting) {
    $meeting = $meeting['meeting'];
    $output = [
      '#type' => 'container',
      '#attributes' => ['class' => ['bbb-meeting-details']],
    ];
    if ($meeting->welcome) {
      $output['welcome'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['bbb-welcome']],
        'message' => $meeting->welcome,
      ];
    }
    $output['status'] = [
      '#theme' => 'bbb_meeting_status',
      'meeting' => $meeting,
    ];

    // Format links according to settings:
    $display_mode = \Drupal::config('bbb.settings')->get('display_mode');

    // Block links.
    $attend = Url::fromRoute('bbb.meeting.attend', ['node' => $meeting->nid], ['absolute' => TRUE]);
    $attend_options = [];
    $moderate = Url::fromRoute('bbb.meeting.moderate', ['node' => $meeting->nid], ['absolute' => TRUE]);
    $moderate_options = [];
    $nolink = Url::fromRoute('entity.node.canonical', ['node' => $meeting->nid], ['absolute' => TRUE]);

    if ($display_mode === 'blank') {
      $attend_options = [
        'onClick' => 'window.open(\'' . $attend->toString() . '\');return false',
        'html' => TRUE,
      ];
      $moderate_options = [
        'onClick' => 'window.open(\'' . $moderate->toString() . '\');return false',
        'html' => TRUE,
      ];
    }

    if ($meeting->dialNumber) {

      $output = [
        '#type' => 'container',
        '#attributes' => ['class' => ['bbb-dial-number']],
        'message' => $this->t('Phone: @number', ['@number' => $meeting->dialNumber]),
      ];
    }
    $user = \Drupal::currentUser();
    if ($user->hasPermission('attend meetings') || $user->hasPermission('administer big blue button')) {
      $output['attend'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['bbb-meeting-attend']],
        'message' => [
          '#type' => 'link',
          '#title' => $this->t('Attend meeting'),
          '#url' => ($display_mode == 'blank') ? $nolink : $attend,
          '#attributes' => $attend_options,
        ],
      ];
    }
    if ($user->hasPermission('moderate meetings') || $user->hasPermission('administer big blue button')) {
      $output['moderate'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['bbb-meeting-moderate']],
        'message' => [
          '#type' => 'link',
          '#title' => $this->t('Moderate meeting'),
          '#url' => ($display_mode == 'blank') ? $nolink : $moderate,
          '#attributes' => $moderate_options,
        ],
      ];
    }
    if ($user->hasPermission('moderate meetings') || $user->hasPermission('administer big blue button')) {
      $output['moderate'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['bbb-meeting-end']],
        'message' => [
          '#type' => 'link',
          '#title' => $this->t('Terminate meeting'),
          '#url' => Url::fromRoute('bbb.meeting.end_meeting_confirm_form', ['node' => $meeting->nid]),
        ],
      ];
    }
    $output['record'] = [
      '#theme' => 'bbb_meeting_record',
      'meeting' => $meeting,
    ];
    return $output;
  }

}
