<?php

namespace Drupal\os2loop_mail_notifications\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure share with a friend admin settings for this site.
 */
final class SettingsForm extends ConfigFormBase {
  use StringTranslationTrait;

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_mail_notifications.settings';

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Settings $settings, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($config_factory);
    $this->settings = $settings;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get(Settings::class),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2loop_mail_notifications_settings';
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

    $form['template_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject template for mail notifications'),
      '#required' => TRUE,
      '#default_value' => $config->get('template_subject'),
    ];

    $form['template_subject_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node', 'os2loop_mail_notifications'],
    ];

    $form['template_body'] = [
      '#type' => 'textarea',
      '#rows' => 20,
      '#title' => $this->t('Body template for mail notifications'),
      '#description' => $this->t('Use "Browse available tokens" to see token related to the actual notification messages.'),
      '#required' => TRUE,
      '#default_value' => $config->get('template_body'),
      '#token_insert' => TRUE,
    ];

    $form['template_body_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node', 'os2loop_mail_notifications'],
    ];

    $userFields = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    $notificationIntervalField = $userFields['os2loop_mail_notifications_intvl'] ?? NULL;
    if (NULL !== $notificationIntervalField) {
      $options = $notificationIntervalField->getSetting('allowed_values');

      $form['default_user_notification_interval'] = [
        '#type' => 'select',
        '#title' => $this->t('Default user notification interval'),
        '#options' => $options,
        '#empty_option' => $this->t('- None -'),
        '#default_value' => $config->get('default_user_notification_interval'),
        '#description' => $this->t('Notification interval to use if user has not set a notification interval.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $template = $form_state->getValue('template_body');
    if (FALSE === strpos($template, '[os2loop_mail_notifications:messages]')
    && FALSE === strpos($template, '[os2loop_mail_notifications:messages_with_headings]')) {
      $form_state->setErrorByName('template_body', $this->t('Please insert either [os2loop_mail_notifications:messages] or [os2loop_mail_notifications:messages_with_headings] in body template.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('template_subject', $form_state->getValue('template_subject'))
      ->set('template_body', $form_state->getValue('template_body'))
      ->set('default_user_notification_interval', $form_state->getValue('default_user_notification_interval'))
      ->save();

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
