<?php

namespace Drupal\os2loop_toc_block\Helper;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\toc_api\TocBuilder;
use Drupal\toc_api\TocManagerInterface;

/**
 * A helper.
 */
class Helper {

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
   * @param \Drupal\toc_api\TocManagerInterface $tocManager
   *   Manager service for table of contents.
   * @param \Drupal\toc_api\TocBuilder $tocBuilder
   *   Builder service for table of contents.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupals renderer service.
   */
  public function __construct(TocManagerInterface $tocManager, TocBuilder $tocBuilder, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    $this->tocManager = $tocManager;
    $this->tocBuilder = $tocBuilder;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * Implements hook_node_view().
   *
   * Adds Ids to headers related to table of contents.
   *
   * @param array $build
   *   The node build.
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display of the node.
   * @param string $view_mode
   *   The view mode of the node.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function nodeView(array &$build, NodeInterface $node, EntityViewDisplayInterface $display, $view_mode) {
    // Add lock to prevent the renderer from calling it self indefinitely.
    $lock = &drupal_static(__FUNCTION__, FALSE);
    if (!$lock) {
      $lock = TRUE;
      if ('full' === $view_mode) {
        $hasToc = FALSE;
        if ($node->hasField('os2loop_documents_document_conte')) {
          $paragraphsContent = $node->get('os2loop_documents_document_conte');
          assert($paragraphsContent instanceof EntityReferenceFieldItemList);
          $paragraphs = $paragraphsContent->referencedEntities();

          foreach ($paragraphs as $paragraph) {
            if ('table_of_contents' == $paragraph->bundle()) {
              $hasToc = TRUE;
              break;
            }
          }
          if ($hasToc) {
            $node_html = (string) $this->renderer->render($build);
            $toc = $this->createToc($node_html);
            if ($toc->isVisible()) {
              $build['#markup'] = $this->tocBuilder->buildContent($toc)['#markup'];
            }
          }
        }
      }
    }
  }

  /**
   * Create table of contents from HTML using TOC API.
   *
   * @param string $node_html
   *   HTML to create table of contents from.
   *
   * @return \Drupal\toc_api\TocInterface
   *   The table of contents.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createToc(string $node_html) {
    // Get 'default' TOC type options.
    $entity_storage = $this->entityTypeManager->getStorage('toc_type');

    /** @var \Drupal\toc_api\Entity\TocType|null $toc_type */
    $toc_type = $entity_storage->load('default');
    $options = !is_null($toc_type) ? $toc_type->getOptions() : [];

    return $this->tocManager->create('toc_filter', $node_html, $options);
  }

}
