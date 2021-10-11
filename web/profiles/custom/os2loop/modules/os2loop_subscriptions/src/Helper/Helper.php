<?php

namespace Drupal\os2loop_subscriptions\Helper;

use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;
use Drupal\os2loop_subscriptions\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\taxonomy\Entity\Term;

/**
 * Helper for os2loop_subscriptions.
 */
class Helper {
  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The database (connection).
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * Constructor.
   */
  public function __construct(Settings $settings, Connection $database) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
    $this->database = $database;
  }

  /**
   * Implements hook_os2loop_settings_is_granted().
   *
   * Handle access for favourite and subscribe flags on node types.
   */
  public function isGranted(string $attribute, $object = NULL): bool {
    if ($object instanceof NodeInterface) {
      if ('favourite' === $attribute) {
        $nodeTypes = $this->config->get('favourite_node_types');
        return $nodeTypes[$object->bundle()] ?? FALSE;
      }
      if ('subscribe' === $attribute) {
        $nodeTypes = $this->config->get('subscribe_node_types');
        return $nodeTypes[$object->bundle()] ?? FALSE;
      }
    }

    return FALSE;
  }

  /**
   * Get ids of users with a subscription on a term.
   *
   * @param \Drupal\taxonomy\Entity\Term $term
   *   The term.
   *
   * @return array
   *   The user ids.
   */
  public function getSubscribedUserIds(Term $term): array {
    return $this->database
      ->select('flagging', 'f')
      ->fields('f', ['uid'])
      ->condition('flag_id', 'os2loop_subscription_term')
      ->condition('entity_id', $term->id())
      ->execute()
      ->fetchCol();
  }

}
