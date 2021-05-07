<?php

namespace Drupal\os2loop_share_with_a_friend\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Share with a friend form.
 *
 * @package Drupal\os2loop_share_with_a_friend\Form
 */
class ShareWithAFriendForm extends FormBase implements ContainerInjectionInterface {
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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a share with a friend form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatcher
   *   The route matcher.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The mail manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(RouteMatchInterface $routeMatcher, MailManagerInterface $mailManager, AccountProxyInterface $currentUser, MessengerInterface $messenger) {
    $this->routeMatcher = $routeMatcher;
    $this->mailManager = $mailManager;
    $this->currentUser = $currentUser;
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
      $container->get('messenger'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'share_with_a_friend_button';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = $this->routeMatcher->getParameter('node');

    $form['title'] = [
      '#type' => 'page_title',
      '#title' => $this->t('Share the following with a friend: @document', ['@document' => $node->label()]),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Write a message to the recipient'),
      '#required' => TRUE,
    ];

    $form['to_email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this->t('Email address of recipient'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#attributes' => [
        'class' => [
          'button',
        ],
      ],
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#url' => new Url('entity.node.canonical', ['node' => $node->id()]),
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
    $to = $form_state->getValue('to_email');
    $module = 'os2loop_share_with_a_friend';
    $key = 'share_with_a_friend';
    $params['message'] = $message;
    $params['node'] = $node;
    $langcode = $this->currentUser->getPreferredLangcode();
    $send = TRUE;
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $this->messenger->addError($this->t('There was a problem sending your message and it was not sent.'), 'error');
    }
    else {
      $this->messenger->addStatus($this->t('Your message has been sent.'));
    }

    $redirectUrl = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $response = new RedirectResponse($redirectUrl);
    $response->send();
  }

}
