<?php

namespace Drupal\os2loop_media_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media\Entity\Media;

/**
 * Media fixture.
 *
 * @package Drupal\os2loop_media_fixtures\Fixture
 */
class MediaFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

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
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   Filesystem.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $files = $this->loadFiles();
    foreach ($files as $file) {
      $mime = $file->getMimeType();
      if ($mime == 'image/png' || $mime == 'image/jpeg') {
        $media = Media::create([
          'bundle' => 'os2loop_media_image',
          'field_media_image' => [
            'target_id' => $file->id(),
            'alt' => sprintf('Description of %s.', $file->getFilename()),
          ],
        ]);
        $media->save();
        $this->setReference('os2loop_image:' . $file->getFilename(), $media);
      }
      else {
        $media = Media::create([
          'bundle' => 'os2loop_media_file',
          'field_media_file' => [
            'target_id' => $file->id(),
          ],
        ]);
      }
      $media->save();
      $this->setReference('os2loop_file:' . $file->getFilename(), $media);
    }
  }

  /**
   * Load all fixture files.
   *
   * @return \Drupal\file\FileInterface[]
   *   The loaded files.
   */
  private function loadFiles(): array {
    $loadedFiles = [];
    $source = __DIR__ . '/../../files';
    $files = $this->fileSystem->scanDirectory($source, '/\.(jpg|png|docx|pdf|txt)$/');
    foreach ($files as $file) {
      $name = $file->filename;
      $destination = 'public://fixtures/files/' . $name;
      if (!is_dir(dirname($destination))) {
        $this->fileSystem->mkdir(dirname($destination), 0755, TRUE);
      }
      $loadedFiles[] = file_save_data(
        file_get_contents($file->uri), $destination,
        FileSystemInterface::EXISTS_REPLACE
      );
    }

    return $loadedFiles;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_media'];
  }

}
