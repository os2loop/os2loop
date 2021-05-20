<?php

namespace Drupal\os2loop_flag_content\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure flag content admin settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS_NAME = 'os2loop_flag_content.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2loop_flag_content_settings';
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS_NAME);

    $form['reasons'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reasons'),
      '#description' => $this->t('Write the possible reasons, separated by a newline'),
      '#default_value' => $config->get('reasons'),
    ];

    $form['to_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address of recipient'),
      '#default_value' => $config->get('to_email'),
    ];

    $form['email_template'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('Email template for flag content body'),
      '#default_value' => $config->get('template_body'),
    ];

    $form['email_template_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node', 'os2loop_flag_content'],
    ];

    $form['subject_template'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('Subject template for flag content subject'),
      '#default_value' => $config->get('template_subject'),
    ];

    $form['subject_template_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node', 'os2loop_flag_content'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $form_state->getValue('content_types');
    $this->configFactory->getEditable(static::SETTINGS_NAME)
      ->set('reasons', $form_state->getValue('reasons'))
      ->set('to_email', $form_state->getValue('to_email'))
      ->set('template_subject', $form_state->getValue('subject_template'))
      ->set('template_body', $form_state->getValue('email_template'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
