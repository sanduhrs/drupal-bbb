<?php

namespace Drupal\bbb\Access;

use Drupal\bbb\Service\NodeMeeting;
use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BBBMeetingModeratorAccessCheck.
 *
 * @package Drupal\bbb\Access
 */
class BBBMeetingModeratorAccessCheck implements AccessCheckInterface {

  /**
   * Node based Meeting API.
   *
   * @var \Drupal\bbb\Service\NodeMeeting
   */
  protected $nodeMeeting;

  /**
   * Constructs a BBBMeetingModeratorAccessCheck object.
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
    return array_key_exists('_bbb_meeting_moderator_access_check', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Request $request, AccountInterface $account) {
    $node = $request->attributes->get('node');;

    if (!$this->nodeMeeting->isTypeOf($node)) {
      return AccessResult::forbidden();
    }

    // Check for access to attend meetings.
    if (
      $account->hasPermission('moderate meetings') ||
      $account->hasPermission('administer big blue button') ||
      (
        $account->id() == $node->getAuthor()->id() &&
        $account->hasPermission('moderate own meetings')
      )
    ) {
      return AccessResult::allowed();
    }
    return AccessResult::neutral();
  }

}
