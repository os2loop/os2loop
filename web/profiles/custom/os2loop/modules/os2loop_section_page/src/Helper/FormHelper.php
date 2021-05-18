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
    if ($formId == 'node_os2loop_section_page_edit_form' || $formId == 'node_os2loop_section_page_form') {
      foreach ($form['os2loop_section_page_paragraph']['widget'] as $widget_key => $widget) {
        if (is_array($widget) && is_numeric($widget_key) && isset($widget['subform'])) {
          $form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']['os2loop_section_page_block']['widget'][0]['settings']['views_label_checkbox']['#access'] = FALSE;
          $form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']['os2loop_section_page_block']['widget'][0]['settings']['views_label_fieldset']['#access'] = FALSE;
          $form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']['os2loop_section_page_block']['widget'][0]['settings']['views_label_field']['#access'] = FALSE;
          $form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']['os2loop_section_page_block']['widget'][0]['settings']['label_display']['#access'] = FALSE;
          // Don't show the view label.
          $form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']['os2loop_section_page_block']['widget'][0]['settings']['label_display']['#default_value'] = FALSE;
          $form['os2loop_section_page_paragraph']['widget'][$widget_key]['subform']['os2loop_section_page_block']['widget'][0]['settings']['label']['#access'] = FALSE;
        }
      }
    }
  }

}
