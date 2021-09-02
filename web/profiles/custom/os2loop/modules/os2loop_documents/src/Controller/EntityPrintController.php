<?php

namespace Drupal\os2loop_documents\Controller;

use Drupal\Console\Core\Utils\NestedArray;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\os2loop_documents\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Entity print controller.
 */
class EntityPrintController extends ControllerBase {
  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(Settings $settings, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME)->get('pdf');
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Settings::class),
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * Render a region.
   */
  public function region(NodeInterface $node, string $region) {
    $image = [];
    switch ($region) {
      case 'header':
        break;

      case 'footer':
        $image['url'] = $this->getFileUrl(['footer_image', 0]);
        break;
    }

    $build[] = [
      '#theme' => 'os2loop_documents_pdf_' . $region,
      '#node' => $node,
      '#image' => $image,
    ];

    $response = new Response();
    $response->setContent($this->renderer->renderRoot($build));

    return $response;
  }

  /**
   * Get file url.
   *
   * @param array $configPath
   *   The config path.
   *
   * @return string|null
   *   The file url if any.
   */
  private function getFileUrl(array $configPath) {
    $fileId = NestedArray::getValue($this->config, $configPath);
    if (NULL === $fileId) {
      return NULL;
    }

    /** @var \Drupal\file\FileInterface|null $file */
    $file = $this->fileStorage->load($fileId);

    return $file ? file_create_url($file->getFileUri()) : NULL;
  }

}
