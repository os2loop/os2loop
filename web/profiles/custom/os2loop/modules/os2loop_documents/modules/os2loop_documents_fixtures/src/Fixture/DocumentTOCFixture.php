<?php

namespace Drupal\os2loop_documents_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Document fixture.
 *
 * @package Drupal\os2loop_documents_fixtures\Fixture
 */
class DocumentTOCFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $document = Node::create([
      'type' => 'os2loop_documents_document',
      'title' => 'Document with table of contents',
      'os2loop_documents_document_autho' => 'Document Author',
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'os2loop_shared_tags' => [
        ['target_id' => $this->getReference('os2loop_tag:test')->id()],
      ],
      'os2loop_shared_profession' => [
        'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
      ],
    ]);

    $paragraph = Paragraph::create([
      'type' => 'table_of_contents',
      'table_of_contents' => 'os2loop_toc_block',
    ]);
    $paragraph->save();
    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_text_and_image',
      'os2loop_documents_title' => 'Far who next them times the our multitude a life',
      'os2loop_documents_tai_text' => [
        'value' => <<<'BODY'
Lots and from touch clear the to her more hearts screen. Brief would affects will his little no in copy how don't the at the reached searched ear I the a in misleads rung as, and attempt, must lead be as chest he want spirit, may history; Dressed be which.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $paragraph->save();
    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

    $document->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      SubjectFixture::class,
      TagFixture::class,
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
