<?php

namespace Drupal\os2loop_oembed\Helper;

use Drupal\Core\Form\FormStateInterface;

/**
 * Helper class for oembed module.
 */
class Helper {

  /**
   * Implements hook_field_widget_WIDGET_TYPE_form_alter().
   *
   * @param array $element
   *   The element being altered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form the element belongs to.
   * @param array $context
   *   The context of the alteration.
   */
  public function paragraphsFormAlter(array &$element, FormStateInterface &$form_state, array $context) {
    if ('os2loop_video' === $element['#paragraph_type']) {
      $sourceTypeField = ':input[name="os2loop_section_page_paragraph[' . $element['#delta'] . '][subform][os2loop_video_source_type]"]';
      $element['subform']['os2loop_video_url']['#states']['visible'] = [
        $sourceTypeField => ['value' => 'url'],
      ];
      $element['subform']['os2loop_video_iframe']['#states']['visible'] = [
        $sourceTypeField => ['value' => 'iframe'],
      ];
    }
  }

}
