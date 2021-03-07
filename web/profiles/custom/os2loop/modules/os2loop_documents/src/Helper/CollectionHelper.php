<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\os2loop_documents\Entity\DocumentCollectionItem;

/**
 * Collection helper.
 */
class CollectionHelper {
  public const CONTENT_TYPE_DOCUMENT = 'os2loop_documents_document';
  public const CONTENT_TYPE_COLLECTION = 'os2loop_documents_collection';

  /**
   * Load collection items from database.
   *
   * @param \Drupal\node\Entity\NodeInterface $collection
   *   The collection.
   *
   * @return \Drupal\os2loop_documents\Entity\DocumentCollectionItem[]
   *   The collection items.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadCollectionItems(NodeInterface $collection) {
    $ids = \Drupal::entityTypeManager()
      ->getStorage('os2loop_document_collection_item')
      ->getQuery()
      ->condition('collection_id', $collection->id())
      ->sort('weight')
      ->execute();

    $items = array_map(DocumentCollectionItem::class . '::load', $ids ?: []);

    // Initialize a variable to store our ordered tree structure.
    $tree = [];
    $this->buildTree($items, $tree);

    return $tree;
  }

  /**
   * Update collection in database.
   */
  public function updateCollection(NodeInterface $node, array $data) {
    $ids = \Drupal::entityTypeManager()
      ->getStorage('os2loop_document_collection_item')
      ->getQuery()
      ->condition('collection_id', $node->id())
      ->execute();
    $items = DocumentCollectionItem::loadMultiple($ids);
    foreach ($items as $item) {
      $item->delete();
    }

    foreach ($data as $row) {
      $item = DocumentCollectionItem::create([
        'collection_id' => $node->id(),
        'document_id' => $row['id'],
        'parent_id' => $row['pid'],
        'weight' => $row['weight'],
      ]);
      $item->save();
    }
  }

  /**
   * Add document to collection.
   *
   * @param \Drupal\node\Entity\NodeInterface $collection
   *   The collection.
   * @param \Drupal\node\Entity\NodeInterface $document
   *   The document.
   * @param \Drupal\node\Entity\NodeInterface|null $parent
   *   The optional document parent.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addDocument(NodeInterface $collection, NodeInterface $document, NodeInterface $parent = NULL) {
    $items = $this->loadCollectionItems($collection);
    $weight = -1;
    foreach ($items as $item) {
      $weight = max($weight, $item->weight->value);
    }
    if (isset($items[$document->id()])) {
      throw new \InvalidArgumentException(sprintf('Document %s is already in collection %s', $document->id(), $collection->id()));
    }
    if (NULL !== $parent) {
      if (!isset($items[$parent->id()])) {
        throw new \InvalidArgumentException(sprintf('Parent document %s is not in collection %s', $parent->id(),
          $collection->id()));
      }
      if ($parent->id() === $document->id()) {
        throw new \InvalidArgumentException(sprintf('Cannot add document %s as a child of itself', $document->id()));
      }
    }

    $item = DocumentCollectionItem::create([
      'collection_id' => $collection->id(),
      'document_id' => $document->id(),
      'parent_id' => $parent ? $parent->id() : 0,
      'weight' => $weight + 1,
    ])->save();
  }

  /**
   * Remove document from collection.
   *
   * @param \Drupal\node\Entity\NodeInterface $collection
   *   The collection.
   * @param \Drupal\node\Entity\NodeInterface $document
   *   The document.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function removeDocument(NodeInterface $collection, NodeInterface $document) {
    $items = $this->loadCollectionItems($collection);
    if (!isset($items[$document->id()])) {
      throw new \InvalidArgumentException(sprintf('Document %s is not in collection %s', $document->id(), $collection->id()));
    }
    // Delete document and any children.
    foreach ($items as $item) {
      if ((int) $document->id() === (int) $item->document_id->value
        || (int) $document->id() === (int) $item->parent_id->value) {
        $item->delete();
      }
    }
  }

  /**
   * Get collection items.
   *
   * @param array $data
   *   The data.
   *
   * @return array
   *   The collection items.
   */
  public function getCollectionItems(array $data) {
    $this->addDepths($data);
    $this->sortItems($data);
    $nodes = Node::loadMultiple(array_keys($data));
    foreach ($data as &$item) {
      $node = $nodes[$item['id']] ?? NULL;
      $item['node'] = $node;
      $item['name'] = $node ? sprintf('%s (%s)', $node->getTitle(), $node->id()) : $item['id'];
    }

    return $data;
  }

  /**
   * Get collection tree.
   *
   * @param array $data
   *   The data.
   *
   * @return array
   *   The collection tree.
   */
  public function getCollectionTree(array $data) {
    $items = $this->getCollectionItems($data);
    $this->buildTree($items);

    return $items;
  }

  /**
   * Sort items.
   *
   * @param array $items
   *   The itens.
   */
  public function sortItems(array &$items) {
  }

  /**
   * Add depth to items in a list of items with weight and parent id (pid).
   */
  public function addDepths(array &$items, int $parentId = 0, int $parentDepth = -1) {
    foreach ($items as &$item) {
      if ($parentId === (int) $item['pid']) {
        $item['depth'] = $parentDepth + 1;
        $this->addDepths($items, $item['id'], $item['depth']);
      }
    }
  }

  /**
   * Build a tree from a list of items with weight and parent id (pid).
   */
  public function buildTree(array $items, &$tree = [], $depth = 0, $parent = 0) {
    $roots = array_filter($items, static function (DocumentCollectionItem $item) use ($parent) {
      return $item->parent_id->value == $parent;
    });

    foreach ($roots as $root) {
      $id = $root->document_id->value;
      $root->depth = $depth;
      $tree[$id] = $root;
      $this->buildTree($items, $tree, $depth + 1, $id);
    }
  }

  /**
   * Array of term parents keyed by vocabulary ID and child term ID.
   *
   * @var array
   */
  protected $treeParents = [];

  /**
   * Array of term ancestors keyed by vocabulary ID and parent term ID.
   *
   * @var array
   */
  protected $treeChildren = [];

  /**
   * Array of terms in a tree keyed by vocabulary ID and term ID.
   *
   * @var array
   */
  protected $treeTerms = [];

  /**
   * Lifted from Drupal\taxonomy\TermStorage::loadTree().
   */
  public function loadTree(int $collectionId, int $parent = 0, int $max_depth = NULL, bool $load_entities = FALSE) {
    $cache_key = implode(':', func_get_args());
    if (!isset($this->trees[$cache_key])) {
      // We cache trees, so it's not CPU-intensive to call on a term and its
      // children, too.
      if (!isset($this->treeChildren[$collectionId])) {
        $this->treeChildren[$collectionId] = [];
        $this->treeParents[$collectionId] = [];
        $this->treeTerms[$collectionId] = [];
        $result = $this->loadCollectionItems(Node::load($collectionId));
        foreach ($result as $term) {
          $this->treeChildren[$collectionId][$term->parent_id->value][] = $term->document_id->value;
          $this->treeParents[$collectionId][$term->document_id->value][] = $term->parent_id->value;
          $this->treeTerms[$collectionId][$term->document_id->value] = $term;
        }
      }

      // Load full entities, if necessary. The entity controller statically
      // caches the results.
      $term_entities = [];
      if ($load_entities) {
        $term_entities = $this->loadMultiple(array_keys($this->treeTerms[$collectionId]));
      }

      if (NULL === $max_depth) {
        $max_depth = count($this->treeChildren[$collectionId]);
      }
      $tree = [];

      // Keeps track of the parents we have to process, the last entry is used
      // for the next processing step.
      $process_parents = [];
      $process_parents[] = $parent;

      // Loops over the parent terms and adds its children to the tree array.
      // Uses a loop instead of a recursion, because it's more efficient.
      while (count($process_parents)) {
        $parent = array_pop($process_parents);
        // The number of parents determines the current depth.
        $depth = count($process_parents);
        if ($max_depth > $depth && !empty($this->treeChildren[$collectionId][$parent])) {
          $has_children = FALSE;
          $child = current($this->treeChildren[$collectionId][$parent]);
          do {
            if (empty($child)) {
              break;
            }
            $term = $load_entities ? $term_entities[$child] : $this->treeTerms[$collectionId][$child];
            if (isset($this->treeParents[$collectionId][$load_entities ? $term->id() : $term->document_id->value])) {
              // Clone the term so that the depth attribute remains correct
              // in the event of multiple parents.
              $term = clone $term;
            }
            $term->depth = $depth;
            $tid = $load_entities ? $term->id() : $term->document_id->value;
            $tree[] = $term;
            if (!empty($this->treeChildren[$collectionId][$tid])) {
              $has_children = TRUE;

              // We have to continue with this parent later.
              $process_parents[] = $parent;
              // Use the current term as parent for the next iteration.
              $process_parents[] = $tid;

              // Reset pointers for child lists because we step in there more
              // often with multi parents.
              reset($this->treeChildren[$collectionId][$tid]);
              // Move pointer so that we get the correct term the next time.
              next($this->treeChildren[$collectionId][$parent]);
              break;
            }
          } while ($child = next($this->treeChildren[$collectionId][$parent]));

          if (!$has_children) {
            // We processed all terms in this hierarchy-level, reset pointer
            // so that this function works the next time it gets called.
            reset($this->treeChildren[$collectionId][$parent]);
          }
        }
      }
      $this->trees[$cache_key] = $tree;
    }
    return $this->trees[$cache_key];
  }

  /**
   * Get descendants.
   *
   * @param int|array $root
   *   The root.
   * @param array $data
   *   The descendants.
   */
  public function getDescendants($root, array $data): array {
    return [];
  }

  /**
   * Decide if an item has children.
   */
  public function hasChildren($item, array &$data) {
    $itemId = $this->getItemId($item);

    foreach ($data as $datum) {
      if ($itemId === (int) $datum['pid']) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Remove item and descendants.
   *
   * @param int|array $item
   *   The item.
   * @param array $data
   *   The descendants.
   */
  public function removeItem($item, array &$data) {
    $itemId = $this->getItemId($item);
    unset($data[$itemId]);
    $descendants = $this->getDescendants($itemId, $data);
    foreach ($descendants as $id) {
      unset($data[$id]);
    }
  }

  /**
   * Get item id.
   *
   * @param int|array $item
   *   The item.
   *
   * @return int|null
   *   The item id.
   */
  private function getItemId($item) {
    return is_scalar($item) ? $item : ($item['id'] ?? NULL);
  }

}
