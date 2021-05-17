<?php

namespace Drupal\os2loop_documents_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;

/**
 * Document legacy fixture.
 *
 * @package Drupal\os2loop_documents_fixtures\Fixture
 */
class DocumentLegacyFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $document = Node::create([
      'type' => 'os2loop_documents_document',
      'title' => 'A legacy document (with body)',
      'os2loop_documents_document_body' => [
        'value' => <<<'BODY'
<p>This is the legacy content.</p>

<table class="loop-documents-table loop-documents-table-steps">
  <thead><tr><th>Trin</th><th>Handling</th><th>Illustration</th></tr></thead>
  <tbody><tr><td>1</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>2</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>3</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>4</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>5</td><td>&nbsp;</td><td>&nbsp;</td></tr></tbody>
</table>
BODY,
        'format' => 'os2loop_documents_body',
      ],
      'os2loop_documents_document_autho' => 'Legacy Document author',
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
