<?php

namespace Drupal\os2loop_flag_content\Helper;

use Drupal\node\NodeInterface;
use Drupal\os2loop_flag_content\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;

/**
 * Helper for os2loop_flag_content.
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
   * Get node type permissions.
   */
  public function isGranted(string $attribute, $object = NULL): bool {
    if ('contact editorial office' === $attribute && $object instanceof NodeInterface) {
      $nodeTypes = $this->config->get('node_types');
      return $nodeTypes[$object->bundle()] ?? FALSE;
    }

    return FALSE;
  }

}
