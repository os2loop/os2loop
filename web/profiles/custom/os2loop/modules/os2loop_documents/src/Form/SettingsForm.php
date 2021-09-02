<?php

namespace Drupal\os2loop_documents\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form.
 */
class SettingsForm extends ConfigFormBase {
  // @see https://drupal.stackexchange.com/a/238329
  use DependencySerializationTrait;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS_NAME = 'os2loop_documents.settings';

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Settings $settings, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($config_factory);
    $this->settings = $settings;
    $this->fileStorage = $entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get(Settings::class),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2loop_documents_settings';
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

    $form['pdf'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PDF'),
      '#tree' => TRUE,
    ];

    $defaultValues = $config->get('pdf') ?? [];
    $form['pdf']['footer_image'] = [
      '#type' => 'managed_file',
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg png'],
      ],
      '#upload_location' => 'public://os2loop_documents/',
      '#title' => $this->t('Footer image'),
      '#description' => $this->t('Footer image. Allowed formats: jpg and png.'),
      '#default_value' => $defaultValues['footer_image'] ?? NULL,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('pdf', $form_state->getValue('pdf'))
      ->save();

    // Make uploaded file permanent.
    if ($fileId = $form_state->getValue(['pdf', 'footer_image', '0'])) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->fileStorage->load($fileId);
      if (NULL !== $file) {
        $file->setPermanent();
        $file->save();
      }
    }

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
