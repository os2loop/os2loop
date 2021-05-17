<?php

namespace Drupal\os2loop_share_with_a_friend\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure share with a friend admin settings for this site.
 */
class SettingsForm extends ConfigFormBase {
  use StringTranslationTrait;

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_share_with_a_friend.settings';

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
    return 'os2loop_share_with_a_friend_settings';
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

    $form['email_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email template for share with a friend body'),
      '#default_value' => $config->get('template_body'),
    ];

    $form['subject_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Subject template for share with a friend subject'),
      '#default_value' => $config->get('template_subject'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('template_subject', $form_state->getValue('subject_template'))
      ->set('template_body', $form_state->getValue('email_template'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
