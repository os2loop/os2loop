<?php

namespace Drupal\os2loop_documents_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\os2loop_documents\Helper\CollectionHelper;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;

/**
 * Collection fixture.
 *
 * @package Drupal\os2loop_documents_fixtures\Fixture
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
      'os2loop_shared_owner' => 'Document owner',
      'os2loop_shared_version' => '1.0',
      'os2loop_shared_approver' => 'Document approver',
      'os2loop_shared_approval_date' => (new DrupalDateTime('2021-06-02'))->format('Y-m-d'),
      'os2loop_shared_rev_date' => (new DrupalDateTime('2021-08-01'))->format('Y-m-d'),
    ]);
    $collection->save();
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Aaa'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Bbb'), $this->getReference('os2loop_documents_document:Aaa'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Ccc'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Ddd'), $this->getReference('os2loop_documents_document:Ccc'));
    $this->collectionHelper->addDocument($collection, $this->getReference('os2loop_documents_document:Eee'), $this->getReference('os2loop_documents_document:Ddd'));

    $collection = Node::create([
      'nid' => 42,
      'type' => 'os2loop_documents_collection',
      'title' => 'Another collection',
      'os2loop_documents_dc_content' => [
        'value' => <<<'BODY'
<p>This collection shares a document with <a href="/node/87">The first collection</a>.</p>
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
