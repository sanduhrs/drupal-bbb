<?php

namespace Drupal\bbb_node\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the BBBNodeType entity.
 *
 * @ConfigEntityType(
 *   id = "bbb_node_type",
 *   label = @Translation("Big Blue Button Content Type"),
 *   label_collection = @Translation("Big Blue Button Content Types"),
 *   label_singular = @Translation("Big Blue Button content type"),
 *   label_plural = @Translation("Big Blue Button content types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Big Blue Button content type",
 *     plural = "@count Big Blue Button content types",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *     "list_builder" = "Drupal\bbb_node\Controller\BBBNodeTypeListController",
 *     "form" = {
 *       "add" = "Drupal\bbb_node\Form\BBBNodeTypeFormController",
 *       "edit" = "Drupal\bbb_node\Form\BBBNodeTypeFormController",
 *       "delete" = "Drupal\bbb_node\Form\BBBNodeTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "bbb_node_type",
 *   admin_permission = "administer big blue button",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/bigbluebutton/{bbb_node_type}",
 *     "delete-form" = "/admin/structure/bigbluebutton/{bbb_node_type}/delete",
 *     "collection" = "/admin/structure/bigbluebutton",
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "active",
 *     "showLinks",
 *     "showStatus",
 *     "moderatorRequired",
 *     "welcome",
 *     "dialNumber",
 *     "moderatorPW",
 *     "attendeePW",
 *     "logoutURL",
 *     "record",
 *   }
 * )
 */
class BBBNodeType extends ConfigEntityBase implements BBBNodeTypeInterface {

  public $id;

  public $uuid;

  public $label;

  public $active;

  public $showLinks;

  public $showStatus;

  public $moderatorRequired;

  public $welcome;

  public $dialNumber;

  public $moderatorPW;

  public $attendeePW;

  public $logoutURL;

  public $record;

  /**
   * {@inheritdoc}
   */
  public function setId($value) {
    $entity = $this->entityTypeManager()
      ->getStorage('bbb_node_type')
      ->load($value);
    if (empty($entity)) {
      $this->id = $value;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($value) {
    $this->label = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function active() {
    return $this->active;
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($value) {
    $this->active = (bool) $value;
  }

  /**
   * {@inheritdoc}
   */
  public function showLinks() {
    return $this->showLinks;
  }

  /**
   * {@inheritdoc}
   */
  public function setShowLinks($value) {
    $this->showLinks = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function showStatus() {
    return $this->showStatus;
  }

  /**
   * {@inheritdoc}
   */
  public function setShowStatus($value) {
    $this->showStatus = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function moderatorRequired() {
    return $this->moderatorRequired;
  }

  /**
   * {@inheritdoc}
   */
  public function setModeratorRequired($value) {
    $this->moderatorRequired = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function welcome() {
    return $this->welcome;
  }

  /**
   * {@inheritdoc}
   */
  public function setWelcome($value) {
    $this->welcome = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function dialNumber() {
    return $this->dialNumber;
  }

  /**
   * {@inheritdoc}
   */
  public function setDialNumber($value) {
    $this->dialNumber = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function moderatorPW() {
    return $this->moderatorPW;
  }

  /**
   * {@inheritdoc}
   */
  public function setModeratorPW($value) {
    $this->moderatorPW = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function attendeePW() {
    return $this->attendeePW;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttendeePW($value) {
    $this->attendeePW = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function logoutURL() {
    return $this->logoutURL;
  }

  /**
   * {@inheritdoc}
   */
  public function setLogoutURL($value) {
    $this->logoutURL = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function record() {
    return $this->record;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecord($value) {
    $this->record = $value;
  }

}
