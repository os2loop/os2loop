<?php

namespace Drupal\os2loop_question\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\os2loop_question\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;

/**
 * Helper for questions.
 */
class Helper {
  public const MODULE = 'os2loop_question';

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The constructor.
   */
  public function __construct(Settings $settings) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
  }

  /**
   * Implements hook_form_alter().
   *
   * Alter forms related to questions.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    switch ($form_id) {
      case 'comment_os2loop_question_answer_form':
        $this->hidePreviewButton($form, $form_state, $form_id);
        break;

      case 'node_os2loop_question_edit_form':
      case 'node_os2loop_question_form':
        $this->handleTextFormats($form, $form_state, $form_id);
        break;
    }
  }

  /**
   * Handle text formats.
   *
   * @param array $form
   *   The form being altered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   * @param string $form_id
   *   The id of the the form.
   */
  private function handleTextFormats(array &$form, FormStateInterface $form_state, string $form_id) {
    $currentFormat = $form['os2loop_question_content']['widget'][0]['#format'] ?? NULL;
    $useRichText = $this->config->get('enable_rich_text') || 'os2loop_question_rich_text' === $currentFormat;
    if ($useRichText) {
      $form['os2loop_question_content']['widget'][0]['#better_formats']['settings']['allowed_formats'] =
        ['os2loop_question_rich_text' => 'os2loop_question_rich_text'];
      $form["os2loop_question_content"]["widget"][0]["#format"] = 'os2loop_question_rich_text';
    }
    else {
      $form['os2loop_question_content']['widget'][0]['#better_formats']['settings']['allowed_formats'] =
        ['os2loop_question_plain_text' => 'os2loop_question_plain_text'];
      $form["os2loop_question_content"]["widget"][0]["#format"] = 'os2loop_question_plain_text';
    }

    $form['os2loop_question_content']['widget']['#after_build'][] =
      [$this, 'fieldAfterBuild'];
  }

  /**
   * Hide preview button in form.
   *
   * @param array $form
   *   The form being altered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   * @param string $form_id
   *   The id of the the form.
   */
  private function hidePreviewButton(array &$form, FormStateInterface $form_state, string $form_id) {
    $form['actions']['preview']['#access'] = FALSE;
    $form['os2loop_question_answer']['widget']['#after_build'][] =
        [$this, 'fieldAfterBuild'];
  }

  /**
   * Remove help text about format.
   *
   * @param array $form_element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   *   The altered form element.
   */
  public function fieldAfterBuild(array $form_element, FormStateInterface $form_state) {
    if (isset($form_element[0]['format'])) {
      // Hide "about text formats and formatter rules." text.
      unset($form_element[0]['format']['guidelines']);
      unset($form_element[0]['format']['help']);
    }

    return $form_element;
  }

}
