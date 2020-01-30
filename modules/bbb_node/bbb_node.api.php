<?php

/**
 * @file
 * Contains API documentation for bbb_node.
 */

/**
 * Alter of meeting creation.
 *
 * @param \BigBlueButton\Parameters\CreateMeetingParameters $parameters
 *   Meetings parameters.
 * @param \Drupal\node\NodeInterface $node
 *   Related meeting node.
 */
function hook_bbb_node_create_alter(\BigBlueButton\Parameters\CreateMeetingParameters $parameters, \Drupal\node\NodeInterface $node) {
  if ($node->getType() === 'target_meeting_type') {
    $parameters->setMaxParticipants(10);
    $parameters->setDuration(60);
  }
}

/**
 * Alter of meeting creation.
 *
 * @param \BigBlueButton\Parameters\CreateMeetingParameters $parameters
 *   Meetings parameters.
 * @param \Drupal\node\NodeInterface $node
 *   Related meeting node.
 */
function hook_bbb_node_update_alter(\BigBlueButton\Parameters\CreateMeetingParameters $parameters, \Drupal\node\NodeInterface $node) {
  if ($node->getType() === 'target_meeting_type') {
    $parameters->setMaxParticipants(10);
    $parameters->setDuration(60);
  }
}

/**
 * Alter of meeting deletion.
 *
 * @param \Drupal\node\NodeInterface $node
 *   Related meeting node.
 */
function hook_bbb_node_delete_alter(\Drupal\node\NodeInterface $node) {
  if ($node->getType() === 'target_meeting_type') {
    /** @var \Drupal\bbb_node\Service\NodeMeeting $node_meeting */
    $node_meeting = \Drupal::service('bbb_node.meeting');
    /** @var array $meeting_before_delete */
    $meeting_before_delete = $node_meeting->get($node);
  }
}
