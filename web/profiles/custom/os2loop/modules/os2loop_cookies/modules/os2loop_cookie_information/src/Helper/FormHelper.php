<?php

namespace Drupal\os2loop_cookie_information\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_settings\Settings;

/**
 * Form helper.
 */
class FormHelper {
  use StringTranslationTrait;

  /**
   * Config setting name.
   *
   * @var string
   */
  public const SETTINGS_NAME = 'os2loop_cookies.settings';

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * Constructor.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS_NAME,
    ];
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   *
   * Adds details tab and config form to os2loop_cookies settings form.
   */
  public function alterForm(array &$form, FormStateInterface $formState, string $formId) {
    $config = $this->settings->getConfig(static::SETTINGS_NAME);
    $form['os2loop_cookie_information_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Cookie information'),
      '#group' => 'tabs',
    ];

    $form['os2loop_cookie_information_details']['os2loop_cookie_information_script'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Cookie consent script'),
      '#default_value' => $config->get('os2loop_cookie_information_script'),
      '#description' => $this->t('Set consent script'),
      '#cols' => 5,
    ];

    $form['#submit'][] = [$this, 'cookieInformationFormSubmit'];
  }

  /**
   * Custom submit handler for saving Cookie Information configuration.
   *
   * @param array $form
   *   The form that is being submitted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form being submitted.
   */
  public function cookieInformationFormSubmit(array $form, FormStateInterface $form_state) {
    $script = !empty($form_state->getValue('os2loop_cookie_information_script')) ? $form_state->getValue('os2loop_cookie_information_script') : NULL;
    $this->settings->getEditableConfig(static::SETTINGS_NAME)
      ->set('os2loop_cookie_information_script', $script)
      ->save();
  }

}
