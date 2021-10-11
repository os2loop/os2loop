<?php

namespace Drupal\os2loop_alert\Helper;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\node\NodeInterface;
use Drupal\os2loop_subscriptions\Helper\Helper as SubscriptionHelper;
use Drupal\taxonomy\Entity\Term;

/**
 * Entity helper.
 */
class Helper {
  use StringTranslationTrait;

  private const PSEUDO_FIELD_ID = 'os2loop_alert';

  /**
   * The node type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeTypeStorage;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The database (connection).
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The token (replacer).
   *
   * @var \Drupal\Core\Utility\Token
   */
  private $token;

  /**
   * The subscription helper.
   *
   * @var \Drupal\os2loop_subscriptions\Helper\Helper
   */
  protected $subscriptionHelper;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, Connection $database, MailManagerInterface $mailManager, LanguageManagerInterface $languageManager, Token $token, SubscriptionHelper $subscriptionHelper, AccountProxyInterface $currentUser) {
    $this->nodeTypeStorage = $entityTypeManager->getStorage('node_type');
    $this->configFactory = $configFactory;
    $this->database = $database;
    $this->mailManager = $mailManager;
    $this->languageManager = $languageManager;
    $this->token = $token;
    $this->subscriptionHelper = $subscriptionHelper;
    $this->currentUser = $currentUser;
  }

  /**
   * Implements hook_entity_extra_field_info().
   *
   * Adds the os2loop_alert field to all node content types.
   */
  public function entityExtraFieldInfo(): array {
    $extra = [];

    foreach ($this->nodeTypeStorage->loadMultiple() as $bundle) {
      $extra['node'][$bundle->id()]['display'][self::PSEUDO_FIELD_ID] = [
        'label' => $this->t('OS2Loop: Alert'),
        'description' => $this->t('Send out alert about this content'),
        'weight' => 9999,
        'visible' => TRUE,
      ];
    }

    return $extra;
  }

  /**
   * Implements hook_ENTITY_TYPE_view().
   */
  public function nodeView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    if ('node' === $entity->getEntityTypeId() && $display->getComponent(self::PSEUDO_FIELD_ID)) {
      $build[self::PSEUDO_FIELD_ID] = [
        '#type' => 'link',
        '#title' => $this->t('Send out alert about @title', ['@title' => $entity->label()]),
        '#url' => Url::fromRoute('os2loop_alert.alert_form', [
          'node' => $entity->id(),
        ]),
        '#attributes' => [
          'class' => [
            Html::cleanCssIdentifier('os2loop_alert'),
            Html::cleanCssIdentifier('os2loop_alert-send-alert'),
          ],
        ],
      ];
    }
  }

  /**
   * Get number of active users.
   *
   * @return int
   *   The number of users.
   */
  public function getNumberOfUsers(): int {
    return count($this->getUserEmails());
  }

  /**
   * Get mail address of all active users.
   *
   * @param array|null $userIds
   *   The user ids or null to get all users' mail addresses.
   *
   * @return array
   *   The mail addresses.
   */
  public function getUserEmails(array $userIds = NULL): array {
    $query = $this->database
      ->select('users_field_data', 'u')
      ->fields('u', ['mail'])
      // Active users.
      ->condition('status', 1)
      // With a mail (address).
      ->isNotNull('mail');

    if (NULL !== $userIds) {
      $query->condition('uid', $userIds ?: [-1], 'IN');
    }

    return $query
      ->execute()
      ->fetchCol();
  }

  /**
   * Get number of subscribers on a term.
   *
   * @return int
   *   The number of subscribers.
   */
  public function getNumberOfSubscribers(Term $term): int {
    return count($this->getAllSubscriberEmails($term));
  }

  /**
   * Get mail address of all subscribers on a term.
   *
   * @return array
   *   The mail addresses.
   */
  public function getAllSubscriberEmails(Term $term): array {
    $userIds = $this->subscriptionHelper->getSubscribedUserIds($term);

    return $this->getUserEmails($userIds);
  }

  /**
   * Implements hook_mail().
   */
  public function mail(string $key, array &$message, array $params) {
    switch ($key) {
      case 'os2loop_alert':
        $message['from'] = $this->configFactory->get('system.site')->get('mail');
        $message['subject'] = $this->token->replace(
          $params['subject'],
          $params['token_data'] ?? [],
        );
        $message['body'][] = $this->token->replace(
          $params['message'],
          $params['token_data'] ?? [],
        );

        break;
    }
  }

  /**
   * Implements hook_mail_alter().
   */
  public function alterMail(array &$message) {
    if (isset($message['params']['headers'])) {
      foreach ($message['params']['headers'] as $key => $value) {
        $key = ucfirst($key);
        switch ($key) {
          case 'Cc':
          case 'Bcc';
            $message['headers'][$key] = $value;
            break;
        }
      }
    }
  }

  /**
   * Send alert mail.
   *
   * @param string $subject
   *   The subject.
   * @param string $message
   *   The message.
   * @param array $recipients
   *   The recipients.
   * @param array $tokenData
   *   Additional token data.
   */
  public function sendAlertMail(string $subject, string $message, array $recipients, array $tokenData) {
    $module = 'os2loop_alert';
    $key = 'os2loop_alert';
    $params['subject'] = $subject;
    $params['message'] = $message;
    $params['token_data'] = $tokenData;
    // Send to user sending out alert.
    $to = $this->currentUser->getEmail();
    // Bcc all recipients.
    $params['headers']['bcc'] = implode(', ', $recipients);
    $langcode = $this->languageManager->getDefaultLanguage()->getId();
    $send = TRUE;

    return $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
  }

  /**
   * Get subject on a node.
   */
  public function getSubject(NodeInterface $node): ?Term {
    try {
      $list = $node->get('os2loop_shared_subject');
      assert($list instanceof EntityReferenceFieldItemList);
      $subjects = $list->referencedEntities();
      $subject = reset($subjects);
      assert($subject instanceof Term);

      return $subject;
    }
    catch (\Throwable $exception) {
      return NULL;
    }
  }

}
