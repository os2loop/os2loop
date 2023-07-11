<?php

namespace Drupal\os2loop_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\NodeType;
use Drupal\os2loop_settings\Settings;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the OS2Loop settings form.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_settings.settings';

  /**
   * Site config setting name.
   *
   * @var string
   */
  public const SITE_SETTINGS_NAME = 'system.site';

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Settings $settings, PathValidatorInterface $pathValidator) {
    parent::__construct($config_factory);
    $this->settings = $settings;
    $this->pathValidator = $pathValidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get(Settings::class),
      $container->get('path.validator')
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
    // Handle select site settings.
    $siteConfig = $this->settings->getConfig('system.site');
    $form['site_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Site settings'),
    ];
    $form['site_settings']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#default_value' => $siteConfig->get('name'),
    ];
    $form['site_settings']['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#default_value' => $siteConfig->get('mail'),
    ];
    $form['site_settings']['front_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Front page'),
      '#default_value' => $siteConfig->get('page')['front'] ?? NULL,
      '#description' => $this->t('Specify a relative URL, e.g. <code>/node/87</code>, to display as the front page.'),
    ];

    // OS2Loop settings.
    $config = $this->settings->getConfig(static::SETTINGS_NAME);
    $form['content_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Content settings'),
    ];

    $nodeTypes = $this->settings->getContentTypes();
    $options = array_map(static function (NodeType $nodeType) {
      return $nodeType->label();
    }, $nodeTypes);

    $form['content_settings']['node_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types'),
      '#description' => $this->t('Enable content types'),
      '#options' => $options,
      '#default_value' => $config->get('node_type'),
    ];

    $vocabularies = $this->settings->getTaxonomyVocabularies();
    $options = array_map(function (Vocabulary $vocabulary) {
      return sprintf(
        '%s (%s)',
        $vocabulary->label(),
        Link::fromTextAndUrl('edit terms', Url::fromRoute('entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => $vocabulary->id()]))->toString()
      );
    }, $vocabularies);

    $form['content_settings']['taxonomy_vocabulary'] = [
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (($value = $form_state->getValue('front_page')) && $value[0] !== '/') {
      $form_state->setErrorByName('front_page', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('front_page')]));
    }
    if (!$this->pathValidator->isValid($form_state->getValue('front_page'))) {
      $form_state->setErrorByName('front_page', $this->t("Either the path '%path' is invalid or you do not have access to it.", ['%path' => $form_state->getValue('front_page')]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $siteConfig = $this->settings->getEditableConfig(static::SITE_SETTINGS_NAME);
    $siteConfig
      ->set('name', $form_state->getValue('name'))
      ->set('mail', $form_state->getValue('mail'))
      ->set('page', ['front' => $form_state->getValue('front_page')] + $siteConfig->get('page'))
      ->save();

    $this->settings->getEditableConfig()
      ->set('node_type', $form_state->getValue('node_type'))
      ->set('taxonomy_vocabulary', $form_state->getValue('taxonomy_vocabulary'))
      ->set('search_settings', $form_state->getValue('search_settings'))
      ->save();

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
