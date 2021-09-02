<?php

namespace Drupal\os2loop_page_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_media_fixtures\Fixture\MediaFixture;

/**
 * Page fixture.
 *
 * @package Drupal\os2loop_page_fixtures\Fixture
 */
class PageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $page = Node::create([
      'type' => 'os2loop_page',
      'title' => 'The first page',
      'os2loop_page_content' => [
        'value' => <<<'BODY'
This is the very first page!
BODY,
        'format' => 'os2loop_page',
      ],
      'os2loop_page_image' => [
        'target_id' => $this->getReference('os2loop_image:image-001.jpg')->id(),
        'alt' => 'This is an image',
      ],
    ]);
    $page->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_page'];
  }

}
