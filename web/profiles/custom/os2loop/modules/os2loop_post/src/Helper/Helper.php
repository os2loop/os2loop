<?php

namespace Drupal\os2loop_post\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_post\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;

/**
 * Helper for posts.
 */
class Helper {
  use StringTranslationTrait;

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
   * Alter forms related to posts.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    switch ($form_id) {
      case 'comment_os2loop_post_comment_form':
        $this->alterCommentForm($form, $form_state, $form_id);
        break;
    }
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
  private function alterCommentForm(array &$form, FormStateInterface $form_state, string $form_id) {
    $form['actions']['submit']['#value'] = $this->t('Add');
    $form['actions']['preview']['#access'] = FALSE;
    // We must use a static callback here to prevent “LogicException: The
    // database connection is not serializable” here. The exception only occurs
    // when not using English as site language.
    $form['os2loop_post_comment']['widget']['#after_build'][] = [
      $this::class,
      'fieldAfterBuild',
    ];

    $allowAnonymousAuthor = (bool) $this->config->get('allow_anonymous_comment_author');
    if (!$allowAnonymousAuthor) {
      $form['os2loop_comment_anonymous_author']['#access'] = FALSE;
      $form['os2loop_comment_anonymous_author']['widget']['#required'] = FALSE;
      $form['os2loop_comment_anonymous_author']['widget']['#default_value'] = 0;
    }
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
  public static function fieldAfterBuild(array $form_element, FormStateInterface $form_state) {
    if (isset($form_element[0]['format'])) {
      // Hide "about text formats and formatter rules." text.
      unset($form_element[0]['format']['guidelines']);
      unset($form_element[0]['format']['help']);
    }

    return $form_element;
  }

}
