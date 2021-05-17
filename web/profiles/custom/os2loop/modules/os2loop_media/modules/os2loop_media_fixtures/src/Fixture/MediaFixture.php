<?php

namespace Drupal\os2loop_media_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\os2loop_fixtures\Fixture\FileFixture;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media\Entity\Media;

/**
 * Media fixture.
 *
 * @package Drupal\os2loop_media_fixtures\Fixture
 */
class MediaFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MediaFixture constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $files = $this->entityTypeManager->getStorage('file');
    $images = $files->loadByProperties(['filemime' => 'image/png']);
    $pdf = $files->loadByProperties(['filemime' => 'application/pdf']);
    $txt = $files->loadByProperties(['filemime' => 'text/plain']);
    $docx = $files->loadByProperties([
      'filemime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ]);

    $media = Media::create([
      'bundle' => 'os2loop_media_file',
      'field_media_file' => [
        'target_id' => key(array_slice($pdf, 0, 1, TRUE)),
      ],
    ]);
    $media->save();

    $media = Media::create([
      'bundle' => 'os2loop_media_file',
      'field_media_file' => [
        'target_id' => key(array_slice($txt, 0, 1, TRUE)),
      ],
    ]);
    $media->save();

    $media = Media::create([
      'bundle' => 'os2loop_media_file',
      'field_media_file' => [
        'target_id' => key(array_slice($docx, 0, 1, TRUE)),
      ],
    ]);
    $media->save();

    $media = Media::create([
      'bundle' => 'os2loop_media_image',
      'field_media_image' => [
        'target_id' => key(array_slice($images, 0, 1, TRUE)),
        'alt' => 'Image 1',
      ],
    ]);
    $media->save();

    $media = Media::create([
      'bundle' => 'os2loop_media_image',
      'field_media_image' => [
        'target_id' => key(array_slice($images, 1, 1, TRUE)),
        'alt' => 'Image 2',
      ],
    ]);
    $media->save();

    $media = Media::create([
      'bundle' => 'os2loop_media_image',
      'field_media_image' => [
        'target_id' => key(array_slice($images, 2, 1, TRUE)),
        'alt' => 'Image 3',
      ],
    ]);
    $media->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      FileFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_media'];
  }

}
