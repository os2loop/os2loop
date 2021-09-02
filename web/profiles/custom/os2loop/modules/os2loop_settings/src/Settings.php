<?php

namespace Drupal\os2loop_settings;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\os2loop_settings\Form\SettingsForm;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Settings for OS2Loop.
 */
class Settings {
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get config.
   */
  public function getConfig(string $name = NULL) {
    return $this->configFactory->get($name ?? SettingsForm::SETTINGS_NAME);
  }

  /**
   * Get editable config.
   */
  public function getEditableConfig(string $name = NULL) {
    return $this->configFactory->getEditable($name ?? SettingsForm::SETTINGS_NAME);
  }

  /**
   * Get config value.
   *
   * @see Settings::getModuleSetting()
   */
  public function get(string $key, ?string $module) {
    return NULL === $module
      ? $this->getConfig()->get($key)
      : $this->getModuleSetting($module, $key);
  }

  /**
   * Get config value.
   */
  public function getModuleSetting(string $module, string $key) {
    return $this->getConfig()->get($module)[$key] ?? NULL;
  }

  /**
   * Get content types.
   */
  public function getContentTypes() {
    return $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();
  }

  /**
   * Get enabled content types.
   *
   * @return array
   *   The enabled content types (type => type).
   */
  public function getEnabledContentTypes(): array {
    return array_filter($this->get('node_type', NULL) ?: [], static function ($value) {
      return 0 !== $value;
    });
  }

  /**
   * Decide if a content type is enabled.
   *
   * @param string $type
   *   The content type.
   *
   * @return bool
   *   True if the content type is enabled.
   */
  public function isContentTypeEnabled(string $type) {
    return isset($this->getEnabledContentTypes()[$type]);
  }

  /**
   * Get taxonomy vocabularies.
   */
  public function getTaxonomyVocabularies(): array {
    return $this->entityTypeManager
      ->getStorage('taxonomy_vocabulary')
      ->loadMultiple();
  }

  /**
   * Get enabled taxonomy vocabularies.
   *
   * @return array
   *   The enabled taxonomy vocabularies (name => Vocabulary).
   */
  public function getEnabledTaxonomyVocabularies(): array {
    $vocabularies = $this->getTaxonomyVocabularies();
    return array_filter($vocabularies, function (Vocabulary $vocabulary) {
      return !empty($this->get('taxonomy_vocabulary', NULL)[$vocabulary->id()]);
    });
  }

  /**
   * Decide if a taxonomy vocabulary is enabled.
   *
   * @param string $name
   *   The taxonomy vocabulary name.
   *
   * @return bool
   *   True if the taxonomy vocabulary is enabled.
   */
  public function isTaxonomyVocabularyEnabled(string $name) {
    return isset($this->getEnabledTaxonomyVocabularies()[$name]);
  }

}
