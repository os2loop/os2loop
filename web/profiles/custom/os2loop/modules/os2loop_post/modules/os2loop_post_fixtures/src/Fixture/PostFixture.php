<?php

namespace Drupal\os2loop_post_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_fixtures\Fixture\FileFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;

/**
 * Post fixture.
 *
 * @package Drupal\os2loop_post_fixtures\Fixture
 */
class PostFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $post = Node::create([
      'type' => 'os2loop_post',
      'title' => 'The first post',
      'status' => Node::PUBLISHED,
      'field_os2loop_post_content' => [
        'value' => <<<'BODY'
This is the very first post!
BODY,
        'format' => 'os2loop_post_rich_text',
      ],
      'field_os2loop_post_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'field_os2loop_post_tags' => [
        ['target_id' => $this->getReference('os2loop_tag:test')->id()],
        ['target_id' => $this->getReference('os2loop_tag:Udredning')->id()],
      ],
      'field_os2loop_post_professio' => [
        'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
      ],
      'field_os2loop_post_file' => [
        'target_id' => $this->getReference('file:image-001.jpg')->id(),
        'description' => 'See this image!',
      ],
    ]);
    $this->addReference('os2loop_post:the-first-post', $post);
    $post->save();

    $post = Node::create([
      'type' => 'os2loop_post',
      'title' => 'Another post',
      'field_os2loop_post_content' => [
        'value' => <<<'BODY'
This is another post …
BODY,
        'format' => 'os2loop_post_plain_text',
      ],
    ]);
    $this->addReference('os2loop_post:another-post', $post);
    $post->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      FileFixture::class,
      SubjectFixture::class,
      TagFixture::class,
      ProfessionFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_post'];
  }

}
