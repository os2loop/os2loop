<?php

namespace Drupal\os2loop_toc_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\os2loop_toc_block\Helper\Helper;
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
final class TocBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The helper service.
   *
   * @var \Drupal\os2loop_toc_block\Helper\Helper
   */
  protected $helper;

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
   * @param \Drupal\os2loop_toc_block\Helper\Helper $helper
   *   Helper service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TocManagerInterface $tocManager, TocBuilder $tocBuilder, RouteMatchInterface $routeMatch, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer, Helper $helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tocManager = $tocManager;
    $this->tocBuilder = $tocBuilder;
    $this->routeMatch = $routeMatch;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
    $this->helper = $helper;
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
      $container->get(Helper::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Make block title optional.
    $form['label']['#required'] = FALSE;
    $form['label_display']['#type'] = 'hidden';
    $form['label_display']['#value'] = 'visible';

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Allow empty title.
   */
  public function label() {
    return $this->configuration['label'] ?: '';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Add lock to prevent the renderer from calling it self indefinitely.
    $lock = &drupal_static(__FUNCTION__, FALSE);
    $build = [];

    if (!$lock) {
      $lock = TRUE;
      $routeName = $this->routeMatch->getRouteName();
      switch ($routeName) {
        case 'entity_print.view.debug':
        case 'entity_print.view':
          $nodeId = $this->routeMatch->getParameter('entity_id');
          $node = $this->entityTypeManager->getStorage('node')->load($nodeId);
          break;

        default:
          $node = $this->routeMatch->getParameter('node');
          break;
      }

      if (!($node instanceof NodeInterface)) {
        return $build;
      }

      $view_mode = 'full';
      $node_view = $this->entityTypeManager->getViewBuilder('node')->view($node, $view_mode);

      // Get the completely render node HTML.
      $node_html = (string) $this->renderer->render($node_view);

      $toc = $this->helper->createToc($node_html);

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
