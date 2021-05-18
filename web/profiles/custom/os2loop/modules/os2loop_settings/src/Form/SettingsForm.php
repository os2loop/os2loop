<?php

namespace Drupal\os2loop_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\os2loop_settings\Settings;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the OS2Loop settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_settings.settings';

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
    return 'os2loop_settings';
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

    $nodeTypes = $this->settings->getContentTypes();
    $options = array_map(static function (NodeType $nodeType) {
      return $nodeType->label();
    }, $nodeTypes);

    $form['node_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types'),
      '#description' => $this->t('Enable content types'),
      '#options' => $options,
      '#default_value' => $config->get('node_type'),
    ];

    $vocabularies = $this->settings->getTaxonomyVocabularies();
    $options = array_map(static function (Vocabulary $vocabulary) {
      return $vocabulary->label();
    }, $vocabularies);

    $form['taxonomy_vocabulary'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Taxonomy vocabularies'),
      '#description' => $this->t('Enable taxonomy vocabularies'),
      '#options' => $options,
      '#default_value' => $config->get('taxonomy_vocabulary'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->settings->getEditableConfig()
      ->set('node_type', $form_state->getValue('node_type'))
      ->set('taxonomy_vocabulary', $form_state->getValue('taxonomy_vocabulary'))
      ->set('search_settings', $form_state->getValue('search_settings'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
