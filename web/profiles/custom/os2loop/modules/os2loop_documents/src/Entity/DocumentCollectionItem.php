<?php

namespace Drupal\os2loop_documents\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the document collection item.
 *
 * @ContentEntityType(
 *   id = "os2loop_document_collection_item",
 *   label = @Translation("Document collection item"),
 *   base_table = "os2loop_documents_collection_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "collection_id" = "collection_id",
 *     "document_id" = "document_id",
 *   },
 * )
 *
 * @method DocumentCollectionItem create
 * @property \Drupal\Core\Field\FieldItemList parent_id
 * @property \Drupal\Core\Field\FieldItemList document_id
 * @property \Drupal\Core\Field\FieldItemList weight
 */
class DocumentCollectionItem extends ContentEntityBase implements ContentEntityInterface {
  /**
   * The depth in tree.
   *
   * @var int
   */
  public $depth = 0;

  /**
   * Set collection.
   *
   * @param NodeInterface $collection
   *   The collection.
   *
   * @return DocumentCollectionItem
   *   The item.
   */
  public function setCollection(NodeInterface $collection): self {
    return $this->set('collection_id', $collection->id());
  }

  /**
   * Get collection.
   */
  public function getCollection(): NodeInterface {
    return Node::load($this->get('collection_id')->value);
  }

  /**
   * Set document.
   *
   * @param NodeInterface $document
   *   The document.
   *
   * @return DocumentDocumentItem
   *   The item.
   */
  public function setDocument(NodeInterface $document): self {
    return $this->set('document_id', $document->id());
  }

  /**
   * Get document.
   */
  public function getDocument(): NodeInterface {
    return Node::load($this->get('document_id')->value);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Contact entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Advertiser entity.'))
      ->setReadOnly(TRUE);

    $fields['collection_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Collection ID'))
      ->setDescription(t('The ID of the Collection node.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['document_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Document ID'))
      ->setDescription(t('The ID of the Document node.'))
      ->setRequired(TRUE);

    $fields['parent_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Parent document ID'))
      ->setDescription(t('The ID of the parent Document node.'));

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight.'))
      ->setRequired(TRUE);

    return $fields;
  }

}
