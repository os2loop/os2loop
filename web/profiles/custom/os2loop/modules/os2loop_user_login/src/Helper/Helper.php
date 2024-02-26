<?php

namespace Drupal\os2loop_user_login\Helper;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_settings\Settings;
use Drupal\os2loop_user_login\Form\SettingsForm;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper for os2loop_user_login.
 */
class Helper {
  use StringTranslationTrait;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current path stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * Constructor.
   */
  public function __construct(Settings $settings, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, EntityFieldManager $entity_field_manager, MessengerInterface $messenger, RequestStack $requestStack, CurrentPathStack $currentPathStack) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->messenger = $messenger;
    $this->requestStack = $requestStack;
    $this->currentPathStack = $currentPathStack;
  }

  /**
   * Implements hook_form_alter().
   *
   * Show different login options depending on the site configuration.
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    if ('openid_connect_login_form' === $form_id) {
      if (!$this->config->get('show_oidc_login')) {
        $form['#access'] = FALSE;
      }
    }
    elseif ('user_login_form' === $form_id) {
      if (!$this->config->get('show_drupal_login')) {
        $form['#attached']['library'][] = 'os2loop_user_login/user-login-form';

        // Wrap default Drupal login form in an element with a known id
        // (drupal-login) so we can visually hide it.
        foreach ($form as $key => $value) {
          if (0 !== strpos($key, '#')) {
            $form['drupal_login'][$key] = array_merge($value);
            unset($form[$key]);
          }
        }
        $form['drupal_login'] += [
          '#type' => 'fieldset',
          '#title' => $this->t('Drupal login'),
          '#weight' => 100,
          '#attributes' => ['id' => 'drupal-login'],
        ];
      }
    }
  }

  /**
   * Implements hook_preprocess_block().
   */
  public function preprocessBlock(array &$variables) {
    if ('userlogin' === ($variables['elements']['#id'] ?? NULL)) {
      // Disable cache on the userlogin form to prevent redirects when resetting
      // password.
      $variables['#cache']['max-age'] = 0;

      // Ignore default login method when resetting password.
      // Note: CurrentPathStack::getPath() claims to return the path without
      // leading slashes, but seems to return it with a leading slash.
      if (preg_match('@^/?user/(reset|password)@', $this->currentPathStack->getPath())) {
        return;
      }

      // Check of we're coming from password reset page.
      $referer = $this->requestStack->getCurrentRequest()->headers->get('referer', '');
      if (preg_match('@/user/password@', $referer)) {
        return;
      }

      $defaultLoginMethod = $this->config->get('default_login_method');
      switch ($defaultLoginMethod) {
        case 'oidc':
          $variables['default_login_form_id'] = 'openid-connect-login-form';
          break;
      }
    }
  }

  /**
   * Remove "Connected accounts" tab on user profile and edit form.
   *
   * @param array $data
   *   The local tasks data.
   * @param string $route_name
   *   The current route.
   */
  public function alterLocalTasks(array &$data, string $route_name) {
    if ($this->moduleHandler->moduleExists('openid_connect')) {
      if ('entity.user.canonical' === $route_name || 'entity.user.edit_form' === $route_name) {
        foreach ($data['tabs'][0] as $key => $tab) {
          if ('entity.user.openid_connect_accounts' === $key) {
            unset($data['tabs'][0][$key]);
          }
        }
      }
    }
  }

  /**
   * Implements hook_user_login().
   *
   * Show a message to the user about incomplete profile.
   *
   * Forcing the user to go to the profile page using a redirect will be too
   * hard to implement and maintain, så we do this the Drupal way (cf.
   * user_user_login()).
   *
   * @see user_user_login()
   */
  public function userLogin(AccountInterface $account) {
    if (($account instanceof UserInterface) && $this->userHasEmptyRequiredFields($account)) {
      $this->messenger->addWarning(
        $this->t('Your user profile is not complete. Please go to <a href=":user-edit">your profile page</a> and fill in the required fields.',
          [
            ':user-edit' => $account->toUrl('edit-form')->toString(),
          ])
      );
    }
  }

  /**
   * Implements hook_openid_connect_userinfo_alter()
   */
  public function openidConnectUserinfoAlter(array &$userinfo, array $context) {
    $mapping = $this->config->get('claims_mapping');
    // Allow mapping for a specific client.
    if (isset($mapping[$context['plugin_id']])) {
      $mapping = $mapping[$context['plugin_id']];
    }

    if (is_array($mapping)) {
      // Keep only string values (to weed out any client specific mappings).
      $mapping = array_filter($mapping, 'is_string');
      foreach ($mapping as $targetClaim => $sourceClaim) {
        if (empty($userinfo[$targetClaim]) && !empty($userinfo[$sourceClaim])) {
          $userinfo[$targetClaim] = $userinfo[$sourceClaim];
        }
      }
    }
  }

  /**
   * Implements hook_menu_links_discovered_alter().
   */
  public function menuLinksDiscoveredAlter(&$links) {
    if (!empty($this->config->get('hide_logout_menu_item'))) {
      unset($links['os2loop_user.divider_logout'], $links['os2loop_user.logout']);
    }
  }

  /**
   * Check if a user has empty required fields.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   *
   * @return bool
   *   True if the user has empty required fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function userHasEmptyRequiredFields(AccountInterface $account): bool {
    /** @var \Drupal\user\Entity\User $user */
    $user = $this->entityTypeManager->getStorage('user')->load($account->id());
    $fields = $this->entityFieldManager->getFieldDefinitions('user', 'user');

    foreach ($fields as $field_name => $field) {
      if ($field->isRequired() && empty($user->get($field_name)->getValue())) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
