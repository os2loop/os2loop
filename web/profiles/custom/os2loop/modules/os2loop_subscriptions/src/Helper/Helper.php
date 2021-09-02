<?php

namespace Drupal\os2loop_subscriptions\Helper;

use Drupal\node\NodeInterface;
use Drupal\os2loop_subscriptions\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;

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
   * Constructor.
   */
  public function __construct(Settings $settings) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
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

}
