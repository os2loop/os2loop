<?php

namespace Drupal\os2loop_oembed\Helper;

use Drupal\Core\Form\FormStateInterface;

/**
 * Helper class for oembed module.
 */
class Helper {

  /**
   * Implements hook_theme().
   *
   * @param array $existing
   *   An array of existing implementations that may be used for override
   *   purposes.
   * @param string $type
   *   Whether a theme, module, etc. is being processed.
   * @param string $theme
   *   The actual name of theme, module, etc.
   * @param string $path
   *   The directory path of the theme or module.
   *
   * @return array
   *   An associative array of information about theme implementations.
   */
  public function theme(array $existing, string $type, string $theme, string $path) {
    return [
      'os2loop_video_iframe' => [
        'variables' => [
          'video' => NULL,
        ],
      ],
    ];
  }

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
