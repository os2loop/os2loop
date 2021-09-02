<?php

namespace Drupal\os2loop_section_page\Helper;

/**
 * Helper.
 *
 * Hook implementations for os2loop_section_page.
 */
class Helper {

  /**
   * Implements hook_preprocess_node().
   *
   * Add js to nodes.
   */
  public function preprocessNode(array &$variables) {
    if ('os2loop_section_page' === $variables['node']->bundle()) {
      $variables['#attached']['library'][] = 'os2loop_section_page/hide-empty-sections';
    }
  }

}
