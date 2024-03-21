<?php

namespace Drupal\os2loop_documents\Controller;

use Drupal\Console\Core\Utils\NestedArray;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\os2loop_documents\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Entity print controller.
 */
final class EntityPrintController extends ControllerBase {
  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private mixed $config;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $fileStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    public Settings $settings,
    public $entityTypeManager,
    public RendererInterface $renderer,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME)->get('pdf');
    $this->fileStorage = $entityTypeManager->getStorage('file');

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Settings::class),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('file_url_generator')
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

    return $file ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()) : NULL;
  }

}
