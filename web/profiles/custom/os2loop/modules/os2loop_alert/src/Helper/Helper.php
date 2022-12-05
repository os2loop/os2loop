<?php

namespace Drupal\os2loop_alert\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\node\NodeInterface;
use Drupal\os2loop_alert\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\os2loop_subscriptions\Helper\Helper as SubscriptionHelper;
use Drupal\taxonomy\Entity\Term;

/**
 * Entity helper.
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
   * The site config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $siteConfig;

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
  public function __construct(Settings $settings, Connection $database, MailManagerInterface $mailManager, LanguageManagerInterface $languageManager, Token $token, SubscriptionHelper $subscriptionHelper, AccountProxyInterface $currentUser) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
    $this->siteConfig = $settings->getConfig('system.site');
    $this->database = $database;
    $this->mailManager = $mailManager;
    $this->languageManager = $languageManager;
    $this->token = $token;
    $this->subscriptionHelper = $subscriptionHelper;
    $this->currentUser = $currentUser;
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

    $emails = $query
      ->execute()
      ->fetchCol();

    // Return only valid emails.
    return array_filter($emails, fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL));
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
        $message['from'] = $this->siteConfig->get('mail');
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

  /**
   * Implements hook_os2loop_settings_is_granted().
   */
  public function isGranted(string $attribute, $object = NULL): bool {
    if ('send alert about' === $attribute && $object instanceof NodeInterface) {
      $nodeTypes = $this->config->get('node_types');
      return $this->currentUser->hasPermission('os2loop send alert')
        && ($nodeTypes[$object->bundle()] ?? FALSE);
    }

    return FALSE;
  }

}
