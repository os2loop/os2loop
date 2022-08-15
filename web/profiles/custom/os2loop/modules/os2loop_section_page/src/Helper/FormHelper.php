<?php

namespace Drupal\os2loop_section_page\Helper;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form helper.
 */
class FormHelper {

  /**
   * Implements hook_form_alter().
   *
   * Hide section page paragraph block reference options.
   */
  public function alterForm(array &$form, FormStateInterface $formState, string $formId) {
    $alterWidgetSettings = static function (array &$element) {
      if (isset($element['os2loop_section_page_block']['widget'][0]['settings'])) {
        $element['os2loop_section_page_block']['widget'][0]['settings']['views_label_checkbox']['#access'] = FALSE;
        $element['os2loop_section_page_block']['widget'][0]['settings']['views_label_fieldset']['#access'] = FALSE;
        $element['os2loop_section_page_block']['widget'][0]['settings']['views_label_field']['#access'] = FALSE;
        $element['os2loop_section_page_block']['widget'][0]['settings']['label_display']['#access'] = FALSE;
        // Don't show the view label.
        $element['os2loop_section_page_block']['widget'][0]['settings']['label_display']['#default_value'] = FALSE;
        $element['os2loop_section_page_block']['widget'][0]['settings']['label']['#access'] = FALSE;
      }
    };

    if (preg_match('/node_os2loop_section_page(_edit)?_form/', $formId) && isset($form['os2loop_section_page_paragraph']['widget'])) {
      foreach ($form['os2loop_section_page_paragraph']['widget'] as $widget_key => $widget) {
        if (is_array($widget) && is_numeric($widget_key) && isset($widget['subform'])) {
          $alterWidgetSettings($form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']);
        }
      }
    }

    if (preg_match('/paragraph_os2loop_section_page_.+_form/', $formId)) {
      $alterWidgetSettings($form);
    }
  }

}
