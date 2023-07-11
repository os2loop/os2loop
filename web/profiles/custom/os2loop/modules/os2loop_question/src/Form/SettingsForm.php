<?php

namespace Drupal\os2loop_question\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure question settings for this site.
 */
final class SettingsForm extends ConfigFormBase {
  use StringTranslationTrait;

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_question.settings';

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
    return 'os2loop_search_db_settings';
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

    $form['enable_rich_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable rich text in questions'),
      '#description' => $this->t('<strong>Note</strong>: This has effect for new questions only. Existing questions will keep their current text format.'),
      '#default_value' => $config->get('enable_rich_text'),
    ];

    $form['allow_anonymous_question_author'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow anonymous question author'),
      '#description' => $this->t('If set, question authors can choose to hide their name when a question is viewed.'),
      '#default_value' => $config->get('allow_anonymous_question_author'),
    ];

    $form['allow_anonymous_answer_author'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow anonymous answer author'),
      '#description' => $this->t('If set, answer authors can choose to hide their name when an answer is viewed.'),
      '#default_value' => $config->get('allow_anonymous_answer_author'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('enable_rich_text', $form_state->getValue('enable_rich_text'))
      ->set('allow_anonymous_question_author', $form_state->getValue('allow_anonymous_question_author'))
      ->set('allow_anonymous_answer_author', $form_state->getValue('allow_anonymous_answer_author'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
