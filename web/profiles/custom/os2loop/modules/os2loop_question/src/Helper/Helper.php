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
   * Hides field for disabled taxonomies from a form.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    $this->handleTextFormats($form, $form_state, $form_id);
  }

  /**
   * Handle text formats.
   */
  private function handleTextFormats(&$form, FormStateInterface $form_state, $form_id) {
    switch ($form_id) {
      case 'node_os2loop_question_form':
      case 'node_os2loop_question_edit_form':
        $currentFormat = $form['os2loop_question_content']['widget'][0]['#format'] ?? NULL;
        $useRichText = $this->config->get('enable_rich_text') || 'os2loop_question_rich_text' === $currentFormat;
        $form['os2loop_question_content']['widget'][0]['#better_formats']['settings']['allowed_formats'] =
          $useRichText ? ['os2loop_question_rich_text' => 'os2loop_question_rich_text'] : ['os2loop_question_plain_text' => 'os2loop_question_plain_text'];

        break;
    }
  }

}
