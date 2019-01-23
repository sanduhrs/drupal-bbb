<?php

namespace Drupal\bbb\Service;

use BigBlueButton\Parameters\CreateMeetingParameters;
use Drupal\Core\Session\AccountInterface;

/**
 * Class NodeMeeting.
 *
 * @package Drupal\bbb\Service
 */
interface MeetingInterface {

  /**
   * Store meeting.
   *
   * @param string $id
   *   Unique string that represent meeting ID.
   * @param \BigBlueButton\Parameters\CreateMeetingParameters $params
   *   Meeting parameters.
   */
  public function store($id, CreateMeetingParameters $params);

  /**
   * Return a meeting object.
   *
   * @param mixed $id
   *   Meeting ID.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   User instance.
   * @param bool $cached
   *   Flag of returning cached results.
   *
   * @return array
   *   Meeting info.
   */
  public function get($id, AccountInterface $account = NULL, $cached = TRUE);

  /**
   * Update meeting.
   *
   * @param string $id
   *   Meeting ID.
   * @param \BigBlueButton\Parameters\CreateMeetingParameters $params
   *   Meeting params.
   */
  public function update($id, CreateMeetingParameters $params);

  /**
   * Delete meeting.
   *
   * @param string $id
   *   Meeting ID.
   */
  public function delete($id);

  /**
   * Create meeting.
   */
  public function create($id, CreateMeetingParameters $params);

}
