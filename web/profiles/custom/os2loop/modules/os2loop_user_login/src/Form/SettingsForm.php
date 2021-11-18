<?php

namespace Drupal\os2loop_user_login\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure os2loop_user_login.
 */
class SettingsForm extends ConfigFormBase {
  use StringTranslationTrait;

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_user_login.settings';

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Settings $settings) {
    parent::__construct($config_factory);
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get(Settings::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2loop_user_login_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->settings->getConfig(static::SETTINGS_NAME);

    $form['show_drupal_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Drupal login'),
      '#default_value' => $config->get('show_drupal_login'),
      '#description' => $this->t(
        'Show Drupal (username and password) login on user login page. If not enabled, the login form will still be visible if <a href="@login_url"><code>#drupal-login</code></a> is appended to the url (<a href="@login_url">@login_url</a>).',
        [
          '@login_url' => Url::fromRoute('user.login', [], [
            'absolute' => TRUE,
            'fragment' => 'drupal-login',
          ])->toString(),
        ]),
    ];

    $form['show_oidc_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show OpenID Connect login'),
      '#default_value' => $config->get('show_oidc_login'),
      '#description' => $this->t(
        'Show OpenID Connect login button on user login page. Set up proper <a href="@config_url">OpenID Connect configuration</a> before enabling this.',
        [
          '@config_url' => Url::fromRoute('openid_connect.admin_settings')->toString(),
        ]
      ),
    ];

    $form['show_saml_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show SAML login'),
      '#default_value' => $config->get('show_saml_login'),
      '#description' => $this->t(
        'Show SAML login button on user login page. Set up proper <a href="@config_url">SAML configuration</a> before enabling this.',
        [
          '@config_url' => Url::fromRoute('samlauth.samlauth_configure_form')->toString(),
        ]
      ),
    ];

    $options['oidc'] = $this->t('OpenID Connect');
    // @todo handle SAML
    // $options['saml'] = $this->t('SAML');
    $form['default_login_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Default login method'),
      '#options' => $options,
      '#empty_value' => '',
      '#default_value' => $config->get('default_login_method'),
      '#description' => $this->t('The default login method to use. If specified, anonymous users will automatically be logged in with this method.'),
    ];

    $form['hide_logout_menu_item'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide logout menu item'),
      '#default_value' => $config->get('hide_logout_menu_item'),
      '#description' => $this->t('If checked, the "Log out" item in the user menu will be hidden.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('show_drupal_login', $form_state->getValue('show_drupal_login'))
      ->set('show_oidc_login', $form_state->getValue('show_oidc_login'))
      ->set('show_saml_login', $form_state->getValue('show_saml_login'))
      ->set('default_login_method', $form_state->getValue('default_login_method'))
      ->set('hide_logout_menu_item', $form_state->getValue('hide_logout_menu_item'))
      ->save();

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
