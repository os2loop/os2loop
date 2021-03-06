<?php

namespace Drupal\os2loop_documents_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_documents\Helper\CollectionHelper;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;

/**
 * Page fixture.
 *
 * @package Drupal\os2loop_page_fixtures\Fixture
 */
class CollectionFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {
  /**
   * The collection helper.
   *
   * @var \Drupal\os2loop_documents\Helper\CollectionHelper
   */
  private $collectionHelper;

  /**
   * Constructor.
   */
  public function __construct(CollectionHelper $collectionHelper) {
    $this->collectionHelper = $collectionHelper;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $collection = Node::create([
      'nid' => 87,
      'type' => 'os2loop_documents_collection',
      'title' => 'The first collection',
      'os2loop_documents_dc_content' => [
        'value' => <<<'BODY'
This is the first collection of documents.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'os2loop_shared_tags' => [
        ['target_id' => $this->getReference('os2loop_tag:test')->id()],
        ['target_id' => $this->getReference('os2loop_tag:Udredning')->id()],
      ],
      'os2loop_shared_profession' => [
        'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
      ],
    ]);
    $collection->save();
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Aaa'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Bbb'), $this->getReference('os2loop_documents_document:Aaa'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Ccc'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Ddd'), $this->getReference('os2loop_documents_document:Ccc'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Eee'), $this->getReference('os2loop_documents_document:Ddd'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      SubjectFixture::class,
      TagFixture::class,
      ProfessionFixture::class,
      DocumentFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_documents'];
  }

}
