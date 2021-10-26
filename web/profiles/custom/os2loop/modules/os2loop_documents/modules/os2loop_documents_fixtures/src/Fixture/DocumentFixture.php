<?php

namespace Drupal\os2loop_documents_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_media_fixtures\Fixture\MediaFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Document fixture.
 *
 * @package Drupal\os2loop_documents_fixtures\Fixture
 */
class DocumentFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $document = Node::create([
      'type' => 'os2loop_documents_document',
      'title' => 'The first document',
      'os2loop_documents_document_autho' => 'Document Author',
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

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_highlighted_co',
      'os2loop_documents_title' => 'Important note',
      'os2loop_documents_hc_content' => [
        'value' => <<<'BODY'
    This is an <strong>important</strong> message.
    BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $paragraph->save();
    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_text_and_image',
      'os2loop_documents_title' => 'Text with image',
      'os2loop_documents_tai_image' => [
        'target_id' => $this->getReference('os2loop_image:image-001.jpg')->id(),
        'alt' => 'Look! An image!',
      ],
      'os2loop_documents_tai_text' => [
        'value' => <<<'BODY'
    This is the text, but there is also an image.
    BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $paragraph->save();
    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_step_by_step',
      'os2loop_documents_title' => 'Step by step',
      'os2loop_documents_description' => [
        'value' => <<<'BODY'
These are the steps you need to perform.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);

    $step = Paragraph::create([
      'type' => 'os2loop_documents_step',
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
    The first step.
    BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $step->save();
    $paragraph->get('os2loop_documents_steps')->appendItem($step);

    $step = Paragraph::create([
      'type' => 'os2loop_documents_step',
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
    The second step.
    BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);

    $subStep = Paragraph::create([
      'type' => 'os2loop_documents_step',
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
    The first step under the second step.
    BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $subStep->save();
    $step->get('os2loop_documents_steps')->appendItem($subStep);

    $step->save();
    $paragraph->get('os2loop_documents_steps')->appendItem($step);

    $paragraph->save();

    $step0 = $this->createStep([
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
Step 0.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ], $paragraph);

    $step00 = $this->createStep([
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
Step 0.0.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ], $step0);

    $this->createStep([
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
Step 0.0.0.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ], $step00);

    $this->createStep([
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
Step 0.0.1.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ], $step00);

    $this->createStep([
      'os2loop_documents_step_text' => [
        'value' => <<<'BODY'
Step 0.1.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ], $step0);

    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

    $document->save();

    foreach (['Aaa', 'Bbb', 'Ccc', 'Ddd', 'Eee', 'Fff'] as $title) {
      $document = Node::create([
        'type' => 'os2loop_documents_document',
        'title' => $title,
        'os2loop_documents_document_autho' => 'Document Author',
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

      $paragraph = Paragraph::create([
        'type' => 'os2loop_documents_highlighted_co',
        'os2loop_documents_title' => sprintf('Important note on %s', $document->getTitle()),
        'os2loop_documents_hc_content' => [
          'value' => sprintf('<p>This is the content of %s</p>', $document->getTitle()),
          'format' => 'os2loop_documents_rich_text',
        ],
      ]);
      $paragraph->save();
      $document->get('os2loop_documents_document_conte')->appendItem($paragraph);

      $this->setReference($document->getType() . ':' . $document->getTitle(), $document);
      $document->save();
    }

    $document = Node::create([
      'type' => 'os2loop_documents_document',
      'title' => 'Dokument om cykler',
      'os2loop_documents_document_autho' => 'Egon',
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'os2loop_shared_tags' => [
        ['target_id' => $this->getReference('os2loop_tag:test')->id()],
      ],
    ]);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_text_and_image',
      'os2loop_documents_tai_text' => [
        'value' => <<<'BODY'
Når man kører på cykel møder man ofte andre cykler så derfor skal der være styr på cyklens grej.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $paragraph->save();
    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);
    $document->save();

    $document = Node::create([
      'type' => 'os2loop_documents_document',
      'title' => 'Dokument om cykler',
      'os2loop_documents_document_autho' => 'Egon',
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'os2loop_shared_tags' => [
        ['target_id' => $this->getReference('os2loop_tag:test')->id()],
      ],
    ]);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_text_and_image',
      'os2loop_documents_tai_text' => [
        'value' => <<<'BODY'
Der skal være styr på cyklens grej.
BODY,
        'format' => 'os2loop_documents_rich_text',
      ],
    ]);
    $paragraph->save();
    $document->get('os2loop_documents_document_conte')->appendItem($paragraph);
    $document->save();

    $document = Node::create([
      'type' => 'os2loop_documents_document',
      'title' => 'Dokument med tabeller',
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'os2loop_shared_tags' => [
        ['target_id' => $this->getReference('os2loop_tag:test')->id()],
      ],
    ]);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_documents_table',
      'os2loop_documents_title' => 'En tabel siger mere end 1000 ord',
      'os2loop_documents_tbl_cont' => [
        'value' => <<<'BODY'
<table class="loop-documents-table">
  <thead>
    <tr>
      <th>A</th>
      <th>B</th>
      <th class="number">C</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>2</td>
      <td>3</td>
      <td class="number">5</td>
    </tr>
    <tr>
      <td>7</td>
      <td>11</td>
      <td class="number">13</td>
    </tr>
  </tbody>
</table>
BODY,
        'format' => 'os2loop_documents_table',
      ],
      'os2loop_documents_tbl_desc' => [
        'value' => <<<'BODY'
Dette er en tabel med tal.
BODY,
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
      MediaFixture::class,
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

  /**
   * Create a step.
   *
   * @param array $values
   *   The paragraph values.
   * @param \Drupal\paragraphs\Entity\Paragraph|null $parent
   *   The parent step.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph
   *   The step.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createStep(array $values, Paragraph $parent = NULL): Paragraph {
    $step = Paragraph::create($values + ['type' => 'os2loop_documents_step']);
    $step->save();
    if (NULL !== $parent) {
      switch ($parent->getType()) {
        case 'os2loop_documents_step':
        case 'os2loop_documents_step_by_step';
          $parent->get('os2loop_documents_steps')->appendItem($step);
          $parent->save();
          break;

        default:
          throw new \InvalidArgumentException(sprintf('Invalid step parent type: %s', $parent->getType()));
      }
    }

    return $step;
  }

}
