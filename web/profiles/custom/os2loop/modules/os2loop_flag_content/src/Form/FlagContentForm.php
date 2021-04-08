<?php

namespace Drupal\os2loop_flag_content\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Flag content form.
 *
 * @package Drupal\os2loop_flag_content\Form
 */
class FlagContentForm extends FormBase implements ContainerInjectionInterface {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an flag content form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'flag_form_button';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $flag_config = \Drupal::config('os2loop_flag_content.settings');
    $nid = \Drupal::routeMatch()->getRawParameter('node');

    $form['node'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $config_causes = explode("\n", $flag_config->get('causes'));
    foreach ($config_causes as $cause) {
      $causes[$cause] = $cause;
    }
    $form['cause'] = [
      '#type' => 'select',
      '#title' => $this->t('Årsager'),
      '#required' => TRUE,
      '#options' => $causes,
      '#empty_option' => $this->t('Vælg en årsag'),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
    ];

    $attributes = [
      'class' => [
        'js-form-submit',
        'form-submit',
        'button',
        'btn',
        'btn-primary',
      ],
    ];

    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => "Send",
      '#attributes' => [
        'class' => [
          'js-form-submit',
          'form-submit',
          'button',
          'btn',
          'btn-primary',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('node') ?? $this->routeMatch->getRawParameter('node');
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    $subject = $form_state->getValue('cause');
    $message = $form_state->getValue('message');
    $flag_config = \Drupal::config('os2loop_flag_content.settings');
    $to = $flag_config->get('to_email');
    $to = 'sinejespersen@gmail.com';
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'os2loop_flag_content';
    $key = 'send_file';
    $params['mail_title'] = $subject;
    $params['message'] = $message;
    $params['node_title'] = $node->label();
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    // If ($result['result'] !== TRUE) {
    // drupal_set_message(t('error.'), 'error');
    // }
    // else {
    // drupal_set_message(t('Your message has been sent.'));
    // }.
  }

}
