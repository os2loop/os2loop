<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\editor\Entity\Editor;

/**
 * A helper.
 */
class Helper {
  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Constructor.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Implements hook_ckeditor_css_alter().
   *
   * Injects custom css into CKEditor instances.
   */
  public function alterCkeditorCss(array &$css, Editor $editor) {
    $css[] = $this->moduleHandler->getModule('os2loop_documents')->getPath() . '/css/ckeditor.css';
  }

}
