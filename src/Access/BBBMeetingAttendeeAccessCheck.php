<?php

namespace Drupal\bbb\Access;

use Drupal\bbb\Service\NodeMeeting;
use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BBBMeetingAttendeeAccessCheck.
 *
 * @package Drupal\bbb\Access
 */
class BBBMeetingAttendeeAccessCheck implements AccessCheckInterface {

  /**
   * Node based Meeting API.
   *
   * @var \Drupal\bbb\Service\NodeMeeting
   */
  protected $nodeMeeting;

  /**
   * Constructs a BBBMeetingAttendeeAccessCheck object.
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
  public function applies(Route $route) {
    return array_key_exists('_bbb_meeting_attendee_access_check', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Request $request, AccountInterface $account) {
    $node = $request->attributes->get('node');;
    return AccessResult::allowedIf($node && $this->nodeMeeting->isTypeOf($node))
      ->andIf(
        // Check for access to attend meetings.
        AccessResult::allowedIf($account->hasPermission('attend meetings') || $account->hasPermission('administer big blue button'))
      );
  }

}
