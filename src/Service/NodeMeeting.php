<?php

namespace Drupal\bbb\Service;

use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\EndMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\IsMeetingRunningParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Class NodeMeeting.
 *
 * @package Drupal\bbb\Service
 */
class NodeMeeting {

  use StringTranslationTrait;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * BBB API.
   *
   * @var \Drupal\bbb\Service\Api
   */
  protected $api;

  protected static $meetings = [];

  /**
   * Private key service.
   *
   * @var \Drupal\Core\PrivateKey
   */
  protected $privateKey;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Meetings storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $meetingCollection;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $log;

  public function __construct(
    AccountInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    Api $api,
    PrivateKey $private_key,
    ModuleHandlerInterface $module_handler,
    KeyValueFactoryInterface $keyvalue,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->api = $api;
    $this->privateKey = $private_key;
    $this->moduleHandler = $module_handler;
    $this->meetingCollection = $keyvalue->get('bbb_meetings');
    $this->log = $logger_factory->get('bbb');
  }

  public function encodeInt($string) {
    $ords = [];
    for ($i = 0; $i < mb_strlen($string); $i++) {
      $ords[] = ord(substr($string, $i, 1));
    }
    return implode($ords);
  }

  /**
   * Creating a meeting id.
   *
   * @param null $salt
   *
   * @return string
   */
  public function createId($salt = NULL) {
    return $this->encodeInt($this->privateKey->get()) . $salt;
  }

  /**
   * Check if user is meeting owner.
   */
  public function isOwner(NodeInterface $node, $account = NULL) {
    if (!$account) {
      $account = $this->currentUser;
    }
    return $account->id() === $node->getOwnerId();
  }

  /**
   * Check if node type is meeting.
   */
  public function isTypeOf(NodeInterface $node) {
    /** @var \Drupal\bbb\Entity\BBBNodeTypeInterface $settings */
    $settings = $this->entityTypeManager->getStorage('bbb_node_type')->load($node->getType());
    if (!$settings) {
      return FALSE;
    }
    return (bool) $settings->active();
  }

  /**
   * Return a meeting object.
   */
  public function get(NodeInterface $node, $account = NULL, $cached = TRUE) {
    if (!$account) {
      $account = $this->currentUser;
    }
    $uuid = $node->uuid();
    if (!isset(self::$meetings[$uuid]) || !$cached) {

      /** @var \BigBlueButton\Parameters\CreateMeetingParameters $meeting_created */
      $meeting_created = $this->meetingCollection->get($uuid);
      if ($meeting_created) {
        $meeting_info = $this->api->getMeetingInfo(new GetMeetingInfoParameters($meeting_created->getMeetingId(), $meeting_created->getModeratorPassword()));
        $attend = new JoinMeetingParameters(
          $meeting_created->getMeetingId(),
          property_exists($account, 'name') ? $account->getDisplayName() : $this->t('Anonymous'),
          $meeting_created->getAttendeePassword()
        );
        $moderate = new JoinMeetingParameters(
          $meeting_created->getMeetingId(),
          property_exists($account, 'name') ? $account->getDisplayName() : $this->t('Anonymous'),
          $meeting_created->getModeratorPassword()
        );
        $url = [
          'attend' => $this->api->joinMeeting($attend),
          'moderate' => $this->api->joinMeeting($moderate),
        ];
        // Allow alteration for e.g. access control
        // Just implement hook_bbb_meeting_alter(&$data) {} in your module.
        $this->moduleHandler->alter('bbb_meeting', $meeting);
        // Static cache.
        self::$meetings[$uuid] = [
          'info' => $meeting_info,
          'url' => $url,
        ];
      }
    }
    return isset(self::$meetings[$uuid]) ? self::$meetings[$uuid] : [];
  }

  /**
   * Init meeting.
   */
  public function init(NodeInterface $node, CreateMeetingParameters $params = NULL) {
    if (empty($params)) {
      $params = new CreateMeetingParameters($this->createId($node->uuid()), $node->getTitle());
    }
    /** @var \Drupal\bbb\Entity\BBBNodeTypeInterface $bbb_config */
    $bbb_config = $this->entityTypeManager->getStorage('bbb_node_type')->load($node->getType());

    $params->setMeetingName(
      $params->getMeetingName() ?:
        $node->getTitle()
    );
    $params->setWelcomeMessage(
      $params->getWelcomeMessage() ?:
        $bbb_config->get('welcome') ?:
          $this->t('Welcome to @title', ['@title' => $node->getTitle()])
    );
    $params->setDialNumber(
      $params->getDialNumber() ?:
        $bbb_config->get('dialNumber') ?:
          NULL
    );
    $params->setModeratorPassword(
      $params->getModeratorPassword() ?:
        $bbb_config->get('moderatorPW') ?:
          user_password()
    );
    $params->setAttendeePassword(
      $params->getAttendeePassword() ?:
        $bbb_config->get('attendeePW') ?:
          user_password()
    );
    $logout_url = $bbb_config->get('logoutURL');
    $params->setLogoutUrl(
      $params->getLogoutUrl() ?:
        (
        empty($logout_url) ?
          NULL :
          Url::fromUserInput($bbb_config->get('logoutURL'), ['absolute' => TRUE])
            ->toString()
        )
    );
    $params->setRecord(
      $params->isRecorded() ?:
        (bool) $bbb_config->get('record')
    );
    // This is the PIN that a dial-in user must enter to join the conference.
    // 5-digit value.
    $params->setVoiceBridge($params->getVoiceBridge() ?: random_int(10000, 99999));

    // TODO: Add support for the next values:
    /*$params->setLogo(
      $params->getLogo() ?:
        Url::fromUserInput($bbb_config->get('logoURL'), ['absolute' => TRUE])->toString()
    );*/
    // $params->setAllowStartStopRecording();
    // $params->setAutoStartRecording();
    // $params->setCopyright($params->getCopyright());
    // $params->setDuration($params->getDuration());
    // $params->setEndCallbackUrl();
    // $params->setMaxParticipants($params->getMaxParticipants());
    // $params->setModeratorOnlyMessage($params->getModeratorOnlyMessage());
    // $params->setMuteOnStart($params->isMuteOnStart());
    // $params->setParentMeetingId($params->getParentMeetingId());
    // $params->setWebcamsOnlyForModerator($params->isWebcamsOnlyForModerator());

    // $params->setBreakout($params->isBreakout());
    // $params->setFreeJoin($params->isFreeJoin());
    // $params->setSequence($params->getSequence());
    // $params->setWebVoice($params->getWebVoice());
    return $params;
  }

  /**
   * Update meeting.
   *
   * @param \Drupal\node\NodeInterface $node
   * @param array $params
   */
  public function update(NodeInterface $node, CreateMeetingParameters $params = NULL) {
    // This is a new record if params is empty.
    $params = $this->init($node, $params);
    return $this->meetingCollection->set($node->uuid(), $params);
  }

  /**
   * Create meeting.
   */
  public function create(NodeInterface $node, CreateMeetingParameters $params) {
    $this->init($node, $params);
    if ($data = $this->api->createMeeting($params)) {
      return $data;
    }
    return FALSE;
  }

  /**
   * Store meeting.
   */
  public function store($node, CreateMeetingParameters $params = NULL) {
    return $this->update($node, $params);
  }

  /**
   * Delete meeting.
   */
  public function delete(NodeInterface $node) {
    if ($this->meetingCollection->has($node->uuid())) {
      $this->meetingCollection->delete($node->uuid());
    }
    else {
      $this->log->warning($this->t('Meeting not found during removal: It was removed before manually or never exists.'));
    }
  }



  /**
   *  End Meeting (endMeeting)
   *
   *  Use this to forcibly end a meeting and kick all participants out of the
   *  meeting.
   *
   * @param \BigBlueButton\Parameters\EndMeetingParameters $params
   *    Associative array of additional url parameters. Components:
   *    - meetingToken: STRING The internal meeting token assigned by the API for
   *      this meeting when it was created. Note that either the meetingToken or
   *      the meetingID along with one of the passwords must be passed into this
   *      call in order to determine which meeting to find.
   *    - meetingID: STRING If you specified a meeting ID when calling create,
   *      then you can use either the generated meeting token or your specified
   *      meeting ID to find meetings. This parameter takes your meeting ID.
   *    - password: STRING The moderator password for this meeting. You can not
   *      end a meeting using the attendee password.
   *
   * @return \BigBlueButton\Responses\EndMeetingResponse|BOOLEAN
   *     A string of either “true” or “false” that signals whether the meeting was
   *     successfully ended.
   */
  function end(NodeInterface $node) {
    $meeting = $this->get($node);
    $params = new EndMeetingParameters(
      $meeting->meetingID,
      $meeting->moderatorPW
    );
    $request = $this->api->endMeeting($params);
    return $request;
  }

}
