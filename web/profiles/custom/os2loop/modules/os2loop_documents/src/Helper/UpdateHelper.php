<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Update helper.
 */
class UpdateHelper {
  /**
   * The paragraphs storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $paragraphStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->paragraphStorage = $entityTypeManager->getStorage('paragraph');
  }

  /**
   * Implements hook_update_N().
   *
   * Migrate titles on Highlighted content paragraphs.
   */
  public function update9101(array $sandbox) {
    $paragraphs = $this->paragraphStorage
      ->loadByProperties(['type' => 'os2loop_documents_highlighted_co']);

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    foreach ($paragraphs as $paragraph) {
      // Migrate title value.
      $paragraph->set(
        'os2loop_documents_title',
        $paragraph->get('os2loop_documents_hc_title')->getValue()
      )->save();
    }
  }

}
