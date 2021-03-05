<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\node\NodeInterface;

/**
 * Collection helper.
 */
class CollectionHelper {
  public const CONTENT_TYPE_DOCUMENT = 'os2loop_documents_document';
  public const CONTENT_TYPE_COLLECTION = 'os2loop_documents_collection';

  /**
   * Update collection in database.
   */
  public function updateCollection(NodeInterface $node, array $data) {
    // @todo Do the right stuff.
  }

  /**
   * Add depth to items in a list of items with weight and parent id (pid).
   */
  public function addDepths(array &$items, $parentId, $parentDepth = 0) {
  }

  /**
   * Build a tree from a list of items with weight and parent id (pid).
   */
  public function buildTree(array $items) {

  }

}
