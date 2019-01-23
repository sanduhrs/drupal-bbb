<?php

namespace Drupal\bbb_node\Plugin\Block;

use Drupal\bbb_node\Service\NodeMeeting;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "BBB Meeting details" block.
 *
 * @Block(
 *   id = "bbb_node_login_meeting",
 *   admin_label = @Translation("BBB Meeting Details")
 * )
 */
class BBBLoginMeeting extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Node based Meeting API.
   *
   * @var \Drupal\bbb_node\Service\NodeMeeting
   */
  protected $nodeMeeting;

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('bbb_node.meeting')
    );
  }

  /**
   * BBBLoginMeeting constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   Current route match service.
   * @param \Drupal\bbb_node\Service\NodeMeeting $node_meeting
   *   Node based Meetings API.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResettableStackedRouteMatchInterface $route_match, NodeMeeting $node_meeting) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->nodeMeeting = $node_meeting;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $node = $this->routeMatch->getParameter('node');
    if (!($node && $this->nodeMeeting->isTypeOf($node))) {
      return AccessResult::forbidden();
    }
    return parent::blockAccess($account);
  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    return [
      '#theme' => 'bbb_meeting',
      '#meeting' => $this->nodeMeeting->get(
        $this->routeMatch->getParameter('node')
      ),
    ];
  }

}
