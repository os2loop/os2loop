<?php

namespace Drupal\os2loop_flag_content\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\os2loop_flag_content\Services\ConfigService;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Flag content form.
 *
 * @package Drupal\os2loop_flag_content\Form
 */
class FlagContentForm extends FormBase implements ContainerInjectionInterface {
  /**
   * The route mathcer.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatcher;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Config service for flag content admin settings.
   *
   * @var \Drupal\os2loop_flag_content\Services\ConfigService
   */
  protected $configService;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a flag content form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatcher
   *   The route matcher.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The mail manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\os2loop_flag_content\Services\ConfigService $configService
   *   The config for flag content.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(RouteMatchInterface $routeMatcher, MailManagerInterface $mailManager, AccountProxyInterface $currentUser, ConfigService $configService, MessengerInterface $messenger) {
    $this->routeMatcher = $routeMatcher;
    $this->mailManager = $mailManager;
    $this->currentUser = $currentUser;
    $this->configService = $configService;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('plugin.manager.mail'),
      $container->get('current_user'),
      $container->get('os2loop_flag_content.config_service'),
      $container->get('messenger'),
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
    $flag_config = $this->configService->getFlagContentSettings();
    $node = $this->routeMatcher->getParameter('node');

    $config_reasons = array_values(array_map('trim', explode(PHP_EOL, $flag_config->get('reasons'))));
    $reasons = array_combine($config_reasons, $config_reasons);

    $form['title'] = [
      '#type' => 'page_title',
      '#title' => $this->t('Flag: @document', ['@document' => $node->label()]),
    ];

    $form['reason'] = [
      '#type' => 'select',
      '#title' => $this->t('Reasons'),
      '#required' => TRUE,
      '#options' => $reasons,
      '#empty_option' => $this->t('Pick a reason'),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#attributes' => [
        'class' => [
          'button',
        ],
      ],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => $node->toUrl(),
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => [
          'button',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->routeMatcher->getParameter('node');
    $message = $form_state->getValue('message');
    $flag_config = $this->configService->getFlagContentSettings();
    $to = $flag_config->get('to_email');
    $module = 'os2loop_flag_content';
    $key = 'flag_content';
    $params['reason'] = $form_state->getValue('reason');
    $params['message'] = $message;
    $params['node'] = $node;
    $langcode = $this->currentUser->getPreferredLangcode();
    $send = TRUE;
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $this->messenger->addError($this->t('There was a problem sending your message and it was not sent.'));
    }
    else {
      $this->messenger->addStatus($this->t('Your message has been sent.'));
      $redirectUrl = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
      $response = new RedirectResponse($redirectUrl);
      $response->send();
    }
  }

}
