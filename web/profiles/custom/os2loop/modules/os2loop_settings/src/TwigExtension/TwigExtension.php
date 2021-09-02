<?php

namespace Drupal\os2loop_settings\TwigExtension;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os2loop_settings\Settings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension.
 */
class TwigExtension extends AbstractExtension {
  /**
   * The account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $account;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * Constructor.
   */
  public function __construct(AccountInterface $account, ModuleHandlerInterface $moduleHandler, Settings $settings) {
    $this->account = $account;
    $this->moduleHandler = $moduleHandler;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('is_granted', [$this, 'isGranted']),
      new TwigFunction('get_os2loop_config', [$this, 'getConfig']),
    ];
  }

  /**
   * Is granted.
   *
   * Heavily inspired by
   * https://symfony.com/doc/current/reference/twig_reference.html#is-granted.
   *
   * Examples:
   *
   *   Check for role:           is_granted('editor')
   *   Check for permission:     is_granted('administer nodes')
   *   Check for access on node: is_granted('update', node)
   */
  public function isGranted(string $attribute = NULL, $object = NULL) {
    if (NULL !== $attribute) {
      // If no object is passed we Check for permission or role.
      if (NULL === $object) {
        if ($this->account->hasPermission($attribute)
          || in_array($attribute, $this->account->getRoles(), TRUE)) {
          return TRUE;
        }
      }

      // Check access on object.
      if (($object instanceof ContentEntityBase) && $object->access($attribute, $this->account)) {
        return TRUE;
      }

      // Let others decide.
      $votes = $this->moduleHandler->invokeAll(
        'os2loop_settings_is_granted',
        [
          $attribute,
          $object,
        ]
      );
      // If one computer says "Yes" we say "Yes".
      if (!empty(array_filter($votes))) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Get os2loop config.
   *
   * @param string|null $config_name
   *   Name of the configuration.
   *
   * @return array
   *   Array of default config or specified config.
   */
  public function getConfig(string $config_name = NULL): array {
    return $this->settings->getConfig($config_name)->get();
  }

}
