<?php

namespace Drupal\os2loop_documents_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Document color fixture.
 *
 * @package Drupal\os2loop_documents_fixtures\Fixture
 */
class DocumentColorFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    foreach (range(1, 4) as $color) {
      $document = Node::create([
        'type' => 'os2loop_documents_document',
        'title' => 'A document with color ' . $color,
        'os2loop_documents_document_autho' => 'Document Author',
        'os2loop_shared_subject' => [
          'target_id' => $this->getReference('os2loop_subject:Subject color ' . $color)->id(),
        ],
        'os2loop_shared_tags' => [
          ['target_id' => $this->getReference('os2loop_tag:test')->id()],
        ],
        'os2loop_shared_profession' => [
          'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
        ],
      ]);

      $paragraph = Paragraph::create([
        'type' => 'os2loop_documents_highlighted_co',
        'os2loop_documents_hc_title' => 'Important note',
        'os2loop_documents_hc_content' => [
          'value' => <<<'BODY'
    This is an <strong>important</strong> message.
    BODY,
          'format' => 'os2loop_documents_rich_text',
        ],
      ]);
      $paragraph->save();
      $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

      $document->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      SubjectFixture::class,
      ProfessionFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_documents'];
  }

}
