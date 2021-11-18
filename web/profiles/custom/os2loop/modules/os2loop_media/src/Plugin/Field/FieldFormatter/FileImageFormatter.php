<?php

namespace Drupal\os2loop_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\FileInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'File image' formatter.
 *
 * @FieldFormatter(
 *   id = "File image formatter",
 *   label = @Translation("File as image"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class FileImageFormatter extends FileFormatterBase implements ContainerFactoryPluginInterface {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor for a custom file formatter.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Display file as image');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      assert($file instanceof FileInterface);
      $fileType = $file->getMimeType();
      // If the file is an image use that for later rendering.
      if ('image/jpeg' === $fileType || 'image/png' === $fileType) {
        $image_uri = $file->getFileUri();
      }
      else {
        // If not an image use drupals fallback generic file icon.
        $image_url = drupal_get_path('module', 'media') . '/images/icons/generic.png';
        $image_uri = 'public://styles/media_library/public/media-icons/generic/generic.png';
        // Ensure file exists.
        $style = $this->entityTypeManager->getStorage('image_style')->load('media_library');
        assert($style instanceof ImageStyle);
        $style->createDerivative($image_url, $image_uri);
      }

      $elements[$delta] = [
        '#theme' => 'image_style',
        '#uri' => $image_uri,
        '#style_name' => 'media_library',
        '#alt' => $file->getFilename(),
        '#title' => $file->getFilename(),
      ];
    }

    return $elements;
  }

}
