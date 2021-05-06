<?php

namespace Drupal\os2loop\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the OS2Loop settings form.
 */
class SettingsForm extends ConfigFormBase {
  /**
   * Config settings.
   *
   * @var string
   */
  public const SETTINGS = 'os2loop.settings';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
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
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $nodeTypes = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();
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

    $vocabularies = $this->entityTypeManager
      ->getStorage('taxonomy_vocabulary')
      ->loadMultiple();
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

    $form['os2loop_question'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->t('Question settings'),
      '#states' => [
        'visible' => [
          [':input[name="node_type[os2loop_question]"]' => ['checked' => TRUE]],
        ],
      ],

      'enable_rich_text' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable rich text in questions'),
        '#default_value' => $config->get('os2loop_question.enable_rich_text'),
      ],
    ];

    $form['search_settings'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->t('Search settings'),

      'filter_content_type' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable filter on content type'),
        '#default_value' => $config->get('search_settings.filter_content_type'),
      ],

      'filter_taxonomy_vocabulary' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Taxonomy vocabulary filters'),
        '#description' => $this->t('Enable taxonomy vocabulary filters'),
        '#options' => $options,
        '#default_value' => $config->get('search_settings.filter_taxonomy_vocabulary') ?: [],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // $form_state->setErrorByName('node_type', $this->t('Error message'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('node_type', $form_state->getValue('node_type'))
      ->set('taxonomy_vocabulary', $form_state->getValue('taxonomy_vocabulary'))
      ->set('os2loop_question', $form_state->getValue('os2loop_question'))
      ->set('search_settings', $form_state->getValue('search_settings'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
