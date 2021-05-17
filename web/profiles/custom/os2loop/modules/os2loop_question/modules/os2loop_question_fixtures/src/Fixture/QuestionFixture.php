<?php

namespace Drupal\os2loop_question_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_media_fixtures\Fixture\MediaFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;

/**
 * Question fixture.
 *
 * @package Drupal\os2loop_question_fixtures\Fixture
 */
class QuestionFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $question = Node::create([
      'type' => 'os2loop_question',
      'title' => 'The first question',
      'status' => Node::PUBLISHED,
      'os2loop_question_content' => [
        'value' => <<<'BODY'
This is the very first question!
BODY,
        'format' => 'os2loop_question_rich_text',
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
      'os2loop_question_file' => [
        'target_id' => $this->getReference('os2loop_file:file-bbb.pdf')->id(),
        'description' => 'See this image!',
      ],
    ]);
    $this->addReference('os2loop_question:the-first-question', $question);
    $question->save();

    $question = Node::create([
      'type' => 'os2loop_question',
      'title' => 'Another question',
      'os2loop_question_content' => [
        'value' => <<<'BODY'
This is another question …
BODY,
        'format' => 'os2loop_question_plain_text',
      ],
    ]);
    $this->addReference('os2loop_question:another-question', $question);
    $question->save();
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
    return ['os2loop_question'];
  }

}
