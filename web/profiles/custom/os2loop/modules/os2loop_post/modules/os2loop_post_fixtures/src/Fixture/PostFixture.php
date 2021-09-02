<?php

namespace Drupal\os2loop_post_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_media_fixtures\Fixture\MediaFixture;
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
      'os2loop_post_content' => [
        'value' => <<<'BODY'
This is the very first post!
BODY,
        'format' => 'os2loop_post',
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
      'os2loop_post_file' => [
        'target_id' => $this->getReference('os2loop_file:file-bbb.pdf')->id(),
        'description' => 'See this image!',
      ],
    ]);
    $this->addReference('os2loop_post:the-first-post', $post);
    $post->save();

    $post = Node::create([
      'type' => 'os2loop_post',
      'title' => 'Another post',
      'os2loop_post_content' => [
        'value' => <<<'BODY'
This is another post …
BODY,
        'format' => 'os2loop_post',
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
    return ['os2loop_post'];
  }

}
