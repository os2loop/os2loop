<?php

namespace Drupal\os2loop_analytics\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the OS2Loop analytics settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_analytics.settings';

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
    return 'os2loop_analytics_settings';
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

    $form['matomo_tracking_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Matomo tracking code'),
      '#default_value' => $config->get('matomo_tracking_code'),
      '#description' => $this->t('Set Matomo tracking code'),
      '#cols' => 8,
    ];

    $form['google_analytics_tracking_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Google analytics tracking code'),
      '#default_value' => $config->get('google_analytics_tracking_code'),
      '#description' => $this->t('Set Google Analytics tracking code'),
      '#cols' => 8,
    ];

    $form['siteimprove_tracking_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Siteimprove tracking code'),
      '#default_value' => $config->get('siteimprove_tracking_code'),
      '#description' => $this->t('Set Siteimprove tracking code'),
      '#cols' => 8,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('matomo_tracking_code', $form_state->getValue('matomo_tracking_code'))
      ->set('google_analytics_tracking_code', $form_state->getValue('google_analytics_tracking_code'))
      ->set('siteimprove_tracking_code', $form_state->getValue('siteimprove_tracking_code'))
      ->save();

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
