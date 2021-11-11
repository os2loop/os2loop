<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Node helper.
 */
class NodeHelper {
  use StringTranslationTrait;

  public const CONTENT_TYPE_DOCUMENT = 'os2loop_documents_document';
  public const CONTENT_TYPE_COLLECTION = 'os2loop_documents_collection';

  /**
   * The collection helper.
   *
   * @var CollectionHelper
   */
  private $collectionHelper;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private $cacheTagsInvalidator;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructor.
   */
  public function __construct(CollectionHelper $collectionHelper, RequestStack $requestStack, CacheTagsInvalidatorInterface $cacheTagsInvalidator, MessengerInterface $messenger) {
    $this->collectionHelper = $collectionHelper;
    $this->requestStack = $requestStack;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->messenger = $messenger;
  }

  /**
   * Implements hook_ENTITY_TYPE_update().
   *
   * Invalidates cache for documents and collections whose display depend on the
   * node.
   */
  public function updateNode(NodeInterface $node) {
    if (self::CONTENT_TYPE_COLLECTION === $node->getType()) {
      $this->invalidateCollectionCache($node);
    }
    elseif (self::CONTENT_TYPE_DOCUMENT === $node->getType()) {
      // Display of collections depends on the collection documents.
      $collections = $this->collectionHelper->loadCollections($node);
      foreach ($collections as $collection) {
        $this->invalidateCollectionCache($collection);
      }
    }
  }

  /**
   * Implements hook_preprocess_HOOK().
   *
   * Adds collection table of contents to collection and document views.
   *
   * Adds list of collections to document view (when document is in more than
   * one colletion).
   */
  public function preprocessNode(array &$variables) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $variables['node'];
    if (self::CONTENT_TYPE_COLLECTION === $node->getType()) {
      $items = $this->collectionHelper->loadTree($node->id());
      $items = $this->collectionHelper->buildDocumentTree($items);
      $variables['os2loop_documents_collection_tree'] = $items;
    }
    elseif (self::CONTENT_TYPE_DOCUMENT === $node->getType()) {
      $collections = $this->collectionHelper->loadCollections($node);
      // Check if we have a request for a specific collection.
      $request = $this->requestStack->getCurrentRequest();
      $collectionId = NULL !== $request ? $request->query->get('collection') : NULL;
      if (isset($collections[$collectionId])) {
        $collections = [$collections[$collectionId]];
      }
      if (1 === count($collections)) {
        $collection = reset($collections);
        $items = $this->collectionHelper->loadTree($collection->id());
        $items = $this->collectionHelper->buildDocumentTree($items);
        $variables['os2loop_documents_collection'] = $collection;
        $variables['os2loop_documents_collection_tree'] = $items;
      }
      elseif (count($collections) > 1) {
        $variables['os2loop_documents_collections'] = $collections;
      }

      // Make sure that the "collection" query parameter is considered when
      // caching (cf.
      // https://www.drupal.org/docs/drupal-apis/cache-api/cache-contexts).
      $variables['#cache']['contexts'][] = 'url.query_args:collection';
    }
  }

  /**
   * Implements hook_theme().
   */
  public function theme($existing, $type, $theme, $path) {
    return [
      'os2loop_documents_pdf_header' => [
        'variables' => [
          'node' => NULL,
          'image' => NULL,
        ],
      ],
      'os2loop_documents_pdf_footer' => [
        'variables' => [
          'node' => NULL,
          'image' => NULL,
        ],
      ],
    ];
  }

  /**
   * Invalidate cache for a collection.
   *
   * Display of collection documents depends on the collection so we need to
   * invalidate cache for all documents in the collection.
   *
   * @param \Drupal\node\NodeInterface $collection
   *   The collection.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   An exception.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   An exception.
   */
  private function invalidateCollectionCache(NodeInterface $collection) {
    $items = $this->collectionHelper->loadCollectionItems($collection);
    foreach ($items as $item) {
      $document = $item->getDocument();
      $this->cacheTagsInvalidator->invalidateTags($document->getCacheTags());
    }
  }

}
