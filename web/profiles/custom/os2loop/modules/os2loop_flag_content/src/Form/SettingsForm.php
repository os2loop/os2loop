<?php

namespace Drupal\os2loop_flag_content\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure flag content admin settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS_NAME = 'os2loop_flag_content.settings';

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
    return 'os2loop_flag_content_settings';
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
    $config = $this->config(static::SETTINGS_NAME);

    $form['reasons'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('Reasons'),
      '#description' => $this->t('Write the possible reasons, separated by a newline'),
      '#default_value' => $config->get('reasons'),
    ];

    $form['to_email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this->t('Email address of recipient'),
      '#default_value' => $config->get('to_email'),
    ];

    $form['subject_template'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Subject template'),
      '#default_value' => $config->get('template_subject'),
    ];

    $form['subject_template_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node', 'os2loop_flag_content'],
    ];

    $form['email_template'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('Email template'),
      '#default_value' => $config->get('template_body'),
    ];

    $form['email_template_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node', 'os2loop_flag_content'],
    ];

    $nodeTypes = $this->settings->getContentTypes();
    $options = array_map(static function (NodeType $nodeType) {
      return $nodeType->label();
    }, $nodeTypes);

    $form['node_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable on content types'),
      '#description' => $this->t('Enable flagging on these content types'),
      '#options' => $options,
      '#default_value' => $config->get('node_types') ?: [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $form_state->getValue('content_types');
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('reasons', $form_state->getValue('reasons'))
      ->set('to_email', $form_state->getValue('to_email'))
      ->set('template_subject', $form_state->getValue('subject_template'))
      ->set('template_body', $form_state->getValue('email_template'))
      ->set('node_types', $form_state->getValue('node_types'))
      ->save();

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
