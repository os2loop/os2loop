<?php

namespace Drupal\os2loop_toc_block\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Block constructor for table of contents.
   *
   * @param \Drupal\toc_api\TocManagerInterface $tocManager
   *   Manager service for table of contents.
   * @param \Drupal\toc_api\TocBuilder $tocBuilder
   *   Builder service for table of contents.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   */
  public function __construct(TocManagerInterface $tocManager, TocBuilder $tocBuilder, EntityTypeManagerInterface $entityTypeManager) {
    $this->tocManager = $tocManager;
    $this->tocBuilder = $tocBuilder;
    $this->entityTypeManager = $entityTypeManager;
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
