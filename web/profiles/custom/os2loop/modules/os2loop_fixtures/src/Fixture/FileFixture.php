<?php

namespace Drupal\os2loop_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Image fixture.
 *
 * @package Drupal\os2loop_fixtures\Fixture
 */
class FileFixture extends AbstractFixture implements FixtureGroupInterface {
  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * Constructor.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $source = __DIR__ . '/../../fixtures/images';
    $files = $this->fileSystem->scanDirectory($source, '/\.(jpg|png)$/');
    foreach ($files as $file) {
      $name = $file->filename;
      $destination = 'public://fixtures/images/' . $name;
      if (!is_dir(dirname($destination))) {
        $this->fileSystem->mkdir(dirname($destination), 0755, TRUE);
      }
      $image = file_save_data(file_get_contents($file->uri), $destination,
        FileSystemInterface::EXISTS_REPLACE);

      $this->setReference('file:' . $file->filename, $image);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_file'];
  }

}
