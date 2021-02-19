<?php

namespace Drupal\os2loop_external_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\TagFixture;

/**
 * External fixture.
 *
 * @package Drupal\os2loop_external_fixtures\Fixture
 */
class ExternalFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $external = Node::create([
      'type' => 'os2loop_external',
      'title' => 'The first external',
      'field_os2loop_external_descripti' => [
        'value' => <<<'BODY'
This external resource is really worth reading.
BODY,
        'format' => 'os2loop_external',
      ],
      'field_os2loop_external_url' => [
        'uri' => 'https://google.com/',
      ],
      'field_os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_taxonomy_subject:Diverse')->id(),
      ],
      'field_os2loop_shared_tags' => [
        ['target_id' => $this->getReference('os2loop_taxonomy_tag:test')->id()],
        ['target_id' => $this->getReference('os2loop_taxonomy_tag:Udredning')->id()],
      ],
      'field_os2loop_shared_profession' => [
        'target_id' => $this->getReference('os2loop_taxonomy_profession:Andet')->id(),
      ],
    ]);
    $external->save();
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
    return ['os2loop_external'];
  }

}
