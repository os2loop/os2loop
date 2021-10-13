<?php

namespace Drupal\os2loop_toc_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\toc_api\TocBuilder;
use Drupal\toc_api\TocManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Table of Contents' Block.
 *
 * @Block(
 *   id = "os2loop_toc_block",
 *   admin_label = @Translation("Table of contents"),
 *   category = @Translation("TOC block"),
 * )
 */
class TocBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The toc manager interface.
   *
   * @var \Drupal\toc_api\TocManagerInterface
   */
  protected $tocManager;

  /**
   * The toc builder interface.
   *
   * @var \Drupal\toc_api\TocBuilderInterface
   */
  protected $tocBuilder;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Render\RendererInterface definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Block constructor for table of contents.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $plugin_id
   *   The block id.
   * @param mixed $plugin_definition
   *   The block definition.
   * @param \Drupal\toc_api\TocManagerInterface $tocManager
   *   Manager service for table of contents.
   * @param \Drupal\toc_api\TocBuilder $tocBuilder
   *   Builder service for table of contents.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   Route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupals renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TocManagerInterface $tocManager, TocBuilder $tocBuilder, RouteMatchInterface $routeMatch, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tocManager = $tocManager;
    $this->tocBuilder = $tocBuilder;
    $this->routeMatch = $routeMatch;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * Create method for block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   The block configuration.
   * @param string $plugin_id
   *   The block id.
   * @param mixed $plugin_definition
   *   The block definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('toc_api.manager'),
      $container->get('toc_api.builder'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lock = &drupal_static(__FUNCTION__, FALSE);
    $build = [];

    if (!$lock) {
      $lock = TRUE;
      $node = $this->routeMatch->getParameter('node');
      if (!($node instanceof NodeInterface)) {
        return $build;
      }

      $view_mode = 'full';
      $node_view = $this->entityTypeManager->getViewBuilder('node')->view($node, $view_mode);

      // Get the completely render node HTML.
      $node_html = (string) $this->renderer->render($node_view);

      // Get 'default' TOC type options.
      $entity_storage = $this->entityTypeManager->getStorage('toc_type');

      /** @var \Drupal\toc_api\Entity\TocType|null $toc_type */
      $toc_type = $entity_storage->load('default');
      $options = !is_null($toc_type) ? $toc_type->getOptions() : [];

      $toc = $this->tocManager->create('toc_filter', $node_html, $options);

      // If the TOC is visible (ie has more than X headers),
      // prepare the block build.
      if ($toc->isVisible()) {
        $build = [
          'toc' => $this->tocBuilder->buildToc($toc),
        ];

      }

    }
    return $build;
  }

}
