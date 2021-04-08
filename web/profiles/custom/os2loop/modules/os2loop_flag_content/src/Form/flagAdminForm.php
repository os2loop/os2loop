<?php

namespace Drupal\os2loop_flag_content\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure flag content admin settings for this site.
 */
class FlagAdminForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'os2loop_flag_content.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['causes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Causes'),
      '#default_value' => $config->get('causes'),
    ];

    $form['to_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address of recipient'),
      '#default_value' => $config->get('to_email'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $form_state->getValue('content_types');
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('causes', $form_state->getValue('causes'))
      ->set('content_types', $form_state->getValue('content_types'))
      ->set('to_email', $form_state->getValue('to_email'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
