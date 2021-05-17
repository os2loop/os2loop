<?php

namespace Drupal\os2loop_search_db\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_settings\Settings;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure flag content admin settings for this site.
 */
class SettingsForm extends ConfigFormBase {
  use StringTranslationTrait;

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_search_db.settings';

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

    $vocabularies = $this->settings->getEnabledTaxonomyVocabularies();
    $taxonomyVocabulariesOptions = array_map(static function (Vocabulary $vocabulary) {
      return $vocabulary->label();
    }, $vocabularies);
    $taxonomyVocabulariesOptions = array_filter($taxonomyVocabulariesOptions, function (string $name) {
      return $this->settings->isTaxonomyVocabularyEnabled($name);
    }, ARRAY_FILTER_USE_KEY);

    $form['filter_content_type'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable filter on content type'),
      '#default_value' => $config->get('filter_content_type'),
    ];

    $form['filter_taxonomy_vocabulary'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Taxonomy vocabulary filters'),
      '#description' => $this->t('Enable taxonomy vocabulary filters'),
      '#options' => $taxonomyVocabulariesOptions,
      '#default_value' => $config->get('filter_taxonomy_vocabulary') ?: [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('filter_content_type', $form_state->getValue('filter_content_type'))
      ->set('filter_taxonomy_vocabulary', $form_state->getValue('filter_taxonomy_vocabulary'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
