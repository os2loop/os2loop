<?php

namespace Drupal\os2loop_member_list\Access;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\os2loop_member_list\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\Routing\Route;

/**
 * Custom access check.
 */
class AccessCheck implements AccessCheckInterface {
  /**
   * The module config.
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
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return '/contacts' === $route->getPath();
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    return $this->config->get('enable_member_list') ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
