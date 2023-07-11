<?php

namespace Drupal\os2loop_alert\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\os2loop_alert\Helper\Helper;
use Drupal\os2loop_settings\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alert form.
 */
final class AlertForm extends FormBase {
  use StringTranslationTrait;

  /**
   * The helper.
   *
   * See https://www.drupal.org/project/drupal/issues/3097143#comment-13704423
   * for an explanation of why this prooerty is protected rather than private.
   *
   * @var \Drupal\os2loop_alert\Helper\Helper
   */
  protected $helper;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructor.
   */
  public function __construct(Helper $helper, Settings $settings) {
    $this->helper = $helper;
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
      $container->get(Settings::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2loop_alert_form';
  }

  /**
   * Returns a page title.
   */
  public function getTitle() {
    $node = $this->getNode();

    return $this->t('Send out alert about %title', [
      '%title' => $node->getTitle(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = $this->getNode();
    $subject = $this->helper->getSubject($node);

    if ($form_state->getTemporaryValue('is_sent')) {
      // Don't build the form if alert has been sent.
      return [];
    }

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
      '#default_value' => $this->t('Important message from [site:name]'),
      '#description' => $this->t('Use <code>[site:name]</code> to insert the site name (required). Other useful tokens: <code>[site:url]</code>'),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Write the message you want to send.'),
      '#description' => $this->t('Use <code>[node:url]</code> to insert the content url (required) and <code>[node:title]</code> to insert the content title. Other useful tokens: <code>[site:name]</code> <code>[site:url]</code>'),
      '#default_value' => $this->config->get('message_template') ?: SettingsForm::DEFAULT_MESSAGE_TEMPLATE,
    ];

    $numberOfUsers = $this->helper->getNumberOfUsers();
    $options = [
      'all_users' => $this->formatPlural(
        $numberOfUsers,
        'All users on the site (one user)',
        'All users on the site (@count users)'
      ),
    ];
    if (NULL !== $subject) {
      $numberOfUsers = $this->helper->getNumberOfSubscribers($subject);
      $options['subject_subscribers'] = $this->formatPlural(
        $numberOfUsers,
        'All subscribers on the subject %subject (one user)',
        'All subscribers on the subject %subject (@count users)',
        [
          '%subject' => $subject->getName(),
          '@count' => $numberOfUsers,
        ]
      );
    }
    $form['recipients'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recipients'),
      '#options' => $options,
      '#required' => TRUE,
      '#description' => $this->t(
        'Select recipients of the message. The message will be sent as an email to you and to each recipient via <a href="@bcc_wiki_url">Blind carbon copy</a> to prevent disclosing the recipient list.',
        [
          '@bcc_wiki_url' => 'https://en.wikipedia.org/wiki/Blind_carbon_copy',
        ]
      ),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['preview'] = [
      '#name' => 'preview',
      '#type' => 'submit',
      '#value' => $this->t('Preview message'),
      '#button_type' => 'secondary',
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('entity.node.canonical', [
        'node' => $node->id(),
      ]),
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    $form['actions']['send'] = [
      '#name' => 'send',
      '#type' => 'submit',
      '#value' => $this->t('Send alert'),
      '#button_type' => 'primary',
    ];

    $isPreview = $form_state->getTemporaryValue('is_preview');
    if ($isPreview) {
      $this->buildMessagePreview($form, $form_state);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $subject = $this->getMessageSubject($form_state);
    if (FALSE === strpos($subject, '[site:name]')) {
      $form_state->setErrorByName('subject', $this->t('Subject does not contain <code>[site:name]</code>'));
    }

    $message = $this->getMessageText($form_state);
    if (FALSE === strpos($message, '[node:url]')) {
      $form_state->setErrorByName('message', $this->t('Message does not contain <code>[node:url]</code>'));
    }

    $numberOfRecipients = count($this->getRecipients($form_state));
    if ($numberOfRecipients < 1) {
      $form_state->setErrorByName('recipients', $this->t('Empty list of recipients selected'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $trigger = $form_state->getTriggeringElement();

    switch ($trigger['#name']) {
      case 'send':
        $this->sendAlert($form_state);
        break;

      case 'preview':
      default:
        $form_state
          ->setTemporaryValue('is_preview', TRUE)
          ->setRebuild();
        break;
    }
  }

  /**
   * Send alert.
   */
  private function sendAlert(FormStateInterface $form_state) {
    $subject = $this->getMessageSubject($form_state);
    $message = $this->getMessageText($form_state);
    $recipients = $this->getRecipients($form_state);

    $node = $this->getNode();
    $result = $this->helper->sendAlertMail($subject, $message, $recipients, [
      'node' => $node,
    ]);
    if ($result['result']) {
      $this->messenger()->addMessage(
        $this->formatPlural(
          count($recipients),
          'Your alert on <a href="@url">@title</a> has been sent to one recipient.',
          'Your alert on <a href="@url">@title</a> has been sent to @count recipients.',
          [
            '@url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(),
            '@title' => $node->label(),
          ]
        ));
      $form_state
        ->setTemporaryValue('is_sent', TRUE)
        ->setRebuild();
    }
    else {
      $this->messenger()->addError($this->t('There was a problem sending your alert and it was not sent.'));
      $form_state
        ->setRebuild()
        ->set('submitted', TRUE);
    }

    return $result;
  }

  /**
   * Get node from route match.
   */
  private function getNode(): NodeInterface {
    return $this->getRouteMatch()->getParameter('node');
  }

  /**
   * Get subject from form state.
   */
  private function getMessageSubject(FormStateInterface $form_state): string {
    return $this->getText('subject', $form_state);
  }

  /**
   * Get message from form state.
   */
  private function getMessageText(FormStateInterface $form_state): string {
    return $this->getText('message', $form_state);
  }

  /**
   * Get mail adress of all recipients.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return string[]
   *   The recipients.
   */
  private function getRecipients(FormStateInterface $form_state): array {
    $recipients = $form_state->getValue('recipients');
    switch ($recipients) {
      case 'all_users':
        return $this->helper->getUserEmails();

      case 'subject_subscribers':
        $node = $this->getNode();
        $subject = $this->helper->getSubject($node);
        return $this->helper->getAllSubscriberEmails($subject);
    }

    return [];
  }

  /**
   * Get text from form state.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function getText(string $name, FormStateInterface $form_state): string {
    return $form_state->getValue($name);
  }

  /**
   * Build message preview.
   */
  private function buildMessagePreview(array &$form, FormStateInterface $form_state) {
    $node = $this->getNode();
    $message = [];
    $this->helper->mail('os2loop_alert', $message, [
      'subject' => $form_state->getValue('subject'),
      'message' => $form_state->getValue('message'),
      'token_data' => [
        'node' => $node,
      ],
    ]);

    $form['preview'] = [
      '#tree' => TRUE,
      '#weight' => 9999,
      '#type' => 'fieldset',
      '#title' => $this->t('Message preview'),

      'from' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'style' => 'white-space: pre',
        ],
        '#value' => $this->t('<strong>From</strong>: @from', ['@from' => $message['from']]),
      ],

      'subject' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'style' => 'white-space: pre',
        ],
        '#value' => $this->t('<strong>Subject</strong>: @subject', ['@subject' => $message['subject']]),
      ],

      'body' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'style' => 'white-space: pre',
        ],
        '#value' => $message['body'][0],
      ],
    ];
  }

}
