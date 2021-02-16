<?php

namespace Drupal\os2loop_question_fixtures\Fixture;

use Drupal\comment\Entity\Comment;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Answer fixture.
 *
 * @package Drupal\os2loop_question_fixtures\Fixture
 */
class AnswerFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $comment = Comment::create([
      'comment_type' => 'os2loop_question_answer',
      'entity_type' => 'node',
      'entity_id' => $this->getReference('os2loop_question:the-first-question')->id(),
      'field_name' => 'field_os2loop_question_answers',
      'status' => Comment::PUBLISHED,
      'field_os2loop_question_answer' => [
        'value' => <<<'BODY'
This is the first answer: Just do it!
BODY,
        'format' => 'os2loop_question_answer_plain_text',
      ],
    ]);
    $this->setReference('comment:the-first-comment', $comment);
    $comment->save();

    $comment = Comment::create([
      'comment_type' => 'os2loop_question_answer',
      'entity_type' => 'node',
      'entity_id' => $this->getReference('os2loop_question:the-first-question')->id(),
      'field_name' => 'field_os2loop_question_answers',
      'status' => Comment::PUBLISHED,
      'field_os2loop_question_answer' => [
        'value' => <<<'BODY'
This is an even better answer: <strong>Don't</strong> do it!
BODY,
        'format' => 'os2loop_question_answer_rich_text',
      ],
    ]);
    $comment->save();

    $comment = Comment::create([
      'comment_type' => 'os2loop_question_answer',
      'entity_type' => 'node',
      'entity_id' => $this->getReference('os2loop_question:the-first-question')->id(),
      'pid' => $this->getReference('comment:the-first-comment')->id(),
      'field_name' => 'field_os2loop_question_answers',
      'status' => Comment::PUBLISHED,
      'field_os2loop_question_answer' => [
        'value' => <<<'BODY'
No! The first answer is still the best.
BODY,
        'format' => 'os2loop_question_answer_plain_text',
      ],
    ]);
    $comment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      QuestionFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_question'];
  }

}
