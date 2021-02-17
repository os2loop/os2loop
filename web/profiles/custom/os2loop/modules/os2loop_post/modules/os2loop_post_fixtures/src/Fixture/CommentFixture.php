<?php

namespace Drupal\os2loop_post_fixtures\Fixture;

use Drupal\comment\Entity\Comment;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Comment fixture.
 *
 * @package Drupal\os2loop_post_fixtures\Fixture
 */
class CommentFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $comment = Comment::create([
      'comment_type' => 'os2loop_post_comment',
      'entity_type' => 'node',
      'entity_id' => $this->getReference('os2loop_post:the-first-post')->id(),
      'field_name' => 'field_os2loop_post_comments',
      'status' => Comment::PUBLISHED,
      'field_os2loop_post_comment' => [
        'value' => <<<'BODY'
This is really good news!
BODY,
        'format' => 'os2loop_post_comment_plain_text',
      ],
    ]);
    $this->setReference('comment:the-first-comment', $comment);
    $comment->save();

    $comment = Comment::create([
      'comment_type' => 'os2loop_post_comment',
      'entity_type' => 'node',
      'entity_id' => $this->getReference('os2loop_post:the-first-post')->id(),
      'field_name' => 'field_os2loop_post_comments',
      'status' => Comment::PUBLISHED,
      'field_os2loop_post_comment' => [
        'value' => <<<'BODY'
Yeah, I agree <strong>strongly</strong>!
BODY,
        'format' => 'os2loop_post_comment_rich_text',
      ],
    ]);
    $comment->save();

    $comment = Comment::create([
      'comment_type' => 'os2loop_post_comment',
      'entity_type' => 'node',
      'entity_id' => $this->getReference('os2loop_post:the-first-post')->id(),
      'pid' => $this->getReference('comment:the-first-comment')->id(),
      'field_name' => 'field_os2loop_post_comments',
      'status' => Comment::PUBLISHED,
      'field_os2loop_post_comment' => [
        'value' => <<<'BODY'
Nah, I've had better news …
BODY,
        'format' => 'os2loop_post_comment_plain_text',
      ],
    ]);
    $comment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      PostFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_post'];
  }

}
