<?php

namespace Drupal\os2loop_member_list\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\os2loop_member_list\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Memberlist helper for creating memberlist queries.
 */
class MemberListHelper {
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * Constructor.
   */
  public function __construct(AccountProxyInterface $currentUser, Settings $settings) {
    $this->currentUser = $currentUser;
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
  }

  /**
   * Implements hook_views_query_alter().
   *
   * Change the contact list to display external users if current user is
   * authenticated.
   */
  public function queryAlter(ViewExecutable $view, QueryPluginBase $query) {
    if ('contact_list' === $view->id()) {
      if ($this->currentUser->isAuthenticated()) {
        // @phpstan-ignore-next-line
        foreach ($query->where[1]['conditions'] as $index => $condition) {
          if ('user__os2loop_user_external_list.os2loop_user_external_list_value' === $condition['field']) {
            // @phpstan-ignore-next-line
            unset($query->where[1]['conditions'][$index]);
          }
        }
      }
    }
  }

  /**
   * Implements hook_views_form_alter().
   *
   * If member list config is enabled show form elements on user profile form.
   */
  public function formAlter(&$form, $form_id) {
    if ('user_form' === $form_id) {
      if (isset($form['os2loop_user_internal_list'], $form['os2loop_user_external_list'])) {
        if (1 !== $this->config->get('enable_member_list')) {
          $form['os2loop_user_internal_list']['#access'] = FALSE;
          $form['os2loop_user_external_list']['#access'] = FALSE;
        }
        else {
          // Enable field 'Show in public contact list' only if 'Show in contact
          // list' is checked.
          $form['os2loop_user_external_list']['#states'] = [
            'enabled' => [
              ':input[name="os2loop_user_internal_list[value]"]' => ['checked' => TRUE],
            ],
          ];
        }
      }
    }
  }

}
