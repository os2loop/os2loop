<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * A helper.
 */
class Helper {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly RouteMatchInterface $routeMatch
  ) {
  }

  /**
   * Implements hook_page_attachments().
   *
   * Injects custom CSS into CKEditor5 the Gin way (cf.
   * https://www.drupal.org/docs/contributed-themes/gin-admin-theme/custom-theming#s-module-recommended-way).
   */
  public function pageAttachments(array &$attachments) {
    if (in_array($this->routeMatch->getRouteName(), [
      'node.add',
      'entity.node.edit_form',
      'paragraphs_edit.edit_form',
    ], TRUE)) {
      $attachments['#attached']['library'][] = 'os2loop_documents/ckeditor';
    }
  }

}
