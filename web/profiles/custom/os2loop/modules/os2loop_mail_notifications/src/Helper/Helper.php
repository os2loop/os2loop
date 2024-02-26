<?php

namespace Drupal\os2loop_mail_notifications\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\message\Entity\Message;
use Drupal\node\NodeInterface;
use Drupal\os2loop_mail_notifications\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\user\Entity\User;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;

/**
 * OS2Loop Mail notifications helper.
 */
class Helper {
  public const MODULE = 'os2loop_mail_notifications';

  /**
   * How often to run our cron task in seconds.
   */
  private const CRON_INTERVAL = 24 * 60 * 60;

  private const STATE_LAST_RUN_AT = 'last_run_at';
  private const USER_NOTIFICATION_INTERVAL_FIELD_NAME = 'os2loop_mail_notifications_intvl';
  private const USER_LAST_NOTIFIED_AT = 'last_notified_at';

  /**
   * Message template names.
   *
   * @var string[]
   */
  private static $messageTemplateNames = [
    'os2loop_message_answer_ins',
    'os2loop_message_answer_upd',
    'os2loop_message_collection_ins',
    'os2loop_message_collection_upd',
    'os2loop_message_comment_ins',
    'os2loop_message_comment_upd',
    'os2loop_message_document_ins',
    'os2loop_message_document_upd',
    'os2loop_message_post_ins',
    'os2loop_message_post_upd',
    'os2loop_message_question_ins',
    'os2loop_message_question_upd',
  ];

  /**
   * Subscription flag names.
   *
   * @var string[]
   */
  private static $subscriptionFlagNames = [
    'os2loop_subscription_node',
    'os2loop_subscription_term',
  ];

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * The user data.
   *
   * @var \Drupal\user\UserDataInterface
   */
  private $userData;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The database (connection).
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The mail helper.
   *
   * @var MailHelper
   */
  private $mailHelper;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Helper constructor.
   */
  public function __construct(Settings $settings, StateInterface $state, UserDataInterface $userData, EntityTypeManagerInterface $entityTypeManager, Connection $database, MailHelper $mailHelper, LoggerChannelFactoryInterface $loggerFactory) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
    $this->state = $state;
    $this->userData = $userData;
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $database;
    $this->mailHelper = $mailHelper;
    $this->logger = $loggerFactory->get(static::MODULE);
  }

  /**
   * Implements hook_cron().
   */
  public function cron() {
    $now = new DrupalDateTime('now', 'UTC');
    $lastRunAt = $this->getLastRunAt();

    if ($now->getTimestamp() - $lastRunAt->getTimestamp() < self::CRON_INTERVAL) {
      return;
    }

    $this->sendNotifications($now);

    $this->setLastRunAt($now);
  }

  /**
   * Send notifications.
   */
  public function sendNotifications(DrupalDateTime $now) {
    $notificationUsers = $this->getNotificationUsers();

    foreach ($notificationUsers as $interval => $users) {
      $startTime = DrupalDateTime::createFromTimestamp($now->getTimestamp() - $interval);

      // The messages generated between "now" and "interval seconds ago". Defer
      // loading until actually needed.
      $messages = NULL;
      foreach ($users as $user) {
        if ($this->getUserLastNotifiedAt($user) > $startTime) {
          continue;
        }
        if (NULL === $messages) {
          $messages = $this->getMessages($startTime, $now);
        }
        $userMessages = $this->getUserMessages($user, $messages);
        if (!empty($userMessages)) {
          $groupedMessages = $this->groupMessages($userMessages);
          $success = $this->mailHelper->sendNotification($user, $groupedMessages);
          if ($success) {
            $this->logger->info(sprintf('Notification mail sent to %s', $user->getEmail()));
            $this->setUserLastNotifiedAt($user, $now);
          }
          else {
            $this->logger->error(sprintf('Error sending notification mail to %s', $user->getEmail()));
          }
        }
      }
    }
  }

  /**
   * Implements hook_user_insert().
   *
   * Sets default notification interval on new user if requested.
   */
  public function userInsert(EntityInterface $entity) {
    if ($entity instanceof UserInterface) {
      $default_user_notification_interval = $this->config->get('default_user_notification_interval') ?? '';
      if ('' !== $default_user_notification_interval) {
        $entity
          ->set('os2loop_mail_notifications_intvl', $default_user_notification_interval)
          ->save();
      }
    }
  }

  /**
   * Map from user id to user's last notification time.
   *
   * @var array
   */
  private $userLastNotifiedAt;

  /**
   * Get user last notified at.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The last notification time.
   */
  private function getUserLastNotifiedAt(UserInterface $user) {
    if (NULL === $this->userLastNotifiedAt) {
      $this->userLastNotifiedAt = $this->userData->get(static::MODULE, NULL, self::USER_LAST_NOTIFIED_AT);
    }

    return $this->userLastNotifiedAt[$user->id()] ?? new DrupalDateTime('@0');
  }

  /**
   * Set user last notified at.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   * @param \Drupal\Core\Datetime\DrupalDateTime $notifiedAt
   *   The notification time.
   */
  private function setUserLastNotifiedAt(UserInterface $user, DrupalDateTime $notifiedAt) {
    $this->userData->set(static::MODULE, $user->id(), self::USER_LAST_NOTIFIED_AT, $notifiedAt);
  }

  /**
   * Group messages by type and content.
   *
   * @param \Drupal\message\Entity\Message[] $messages
   *   The messages.
   *
   * @return array
   *   The grouped messages.
   */
  private function groupMessages(array $messages): array {
    $nodeIds = [];
    $groupedMessages = [];
    foreach ($messages as $message) {
      $type = $message->getTemplate()->id();
      $node = $this->getMessageNode($message);
      if (NULL !== $node) {
        if (!isset($nodeIds[$type][$node->id()])) {
          $groupedMessages[$type][$node->id()] = $message;
        }
        $nodeIds[$type][$node->id()] = $node->id();
      }
    }

    // @todo Remove notification on content edited if a the same content has been
    // created since last run.
    return $groupedMessages;
  }

  /**
   * Get messages.
   *
   * @return \Drupal\message\MessageInterface[]
   *   The messages.
   */
  private function getMessages(DrupalDateTime $from, DrupalDateTime $to) {
    $storage = $this->entityTypeManager
      ->getStorage('message');
    $ids = $storage
      ->getQuery()
      ->condition('template', self::$messageTemplateNames, 'IN')
      ->condition('created', [$from->getTimestamp(), $to->getTimestamp()], 'BETWEEN')
      ->sort('created', 'DESC')
      ->accessCheck()
      ->execute();

    // @phpstan-ignore-next-line
    return $storage->loadMultiple($ids);
  }

  /**
   * Get users that may receive notifications.
   *
   * Get users that subscribe to content or taxonomy terms and have set a
   * notification interval.
   *
   * @return array
   *   The users grouped by notification interval.
   */
  private function getNotificationUsers() {
    $subscriptionUserIds = $this->database
      ->select('flagging', 'f')
      ->fields('f', ['uid'])
      ->condition('flag_id', self::$subscriptionFlagNames, 'IN')
      ->execute()
      ->fetchAllKeyed(0, 0);

    $notificationUserIds = $this->entityTypeManager
      ->getStorage('user')
      ->getQuery()
      ->condition(self::USER_NOTIFICATION_INTERVAL_FIELD_NAME, 0, '>')
      ->accessCheck()
      ->execute();

    // Group users by notification interval.
    $groups = [];
    // @phpstan-ignore-next-line
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple(array_intersect($subscriptionUserIds, $notificationUserIds));
    foreach ($users as $user) {
      $groups[$this->getNotificationInterval($user)][] = $user;
    }

    return $groups;
  }

  /**
   * Get messages relevant for a user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user.
   * @param \Drupal\message\Entity\Message[] $messages
   *   All candidate messages.
   *
   * @return \Drupal\message\Entity\Message[]
   *   The messages for the user.
   */
  private function getUserMessages(User $user, array $messages): array {
    return array_filter($messages, function (Message $message) use ($user) {
      // Exclude messages generated by own actions.
      if ($message->getOwner() === $user) {
        return FALSE;
      }

      $node = $this->getMessageNode($message);
      if (NULL !== $node) {
        $userNodeIds = $this->getSubscribedNodeIds($user);
        if (in_array($node->id(), $userNodeIds, TRUE)) {
          return TRUE;
        }

        $subjectId = $node->get('os2loop_shared_subject')->getValue()[0]['target_id'] ?? NULL;
        if (NULL !== $subjectId) {
          $userSubjectIds = $this->getSubscribedTaxonomyTermIds($user);
          if (in_array($subjectId, $userSubjectIds, TRUE)) {
            return TRUE;
          }
        }
      }

      return FALSE;
    });
  }

  /**
   * Map from user id to nodes ids.
   *
   * @var array
   */
  private $userNodeIds;

  /**
   * Get ids of nodes a user subscribes to.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user.
   *
   * @return int[]
   *   The node ids.
   */
  private function getSubscribedNodeIds(User $user): array {
    if (NULL === $this->userNodeIds) {
      $result = $this->database
        ->select('flagging', 'f')
        ->fields('f', ['uid', 'entity_id'])
        ->condition('flag_id', 'os2loop_subscription_node')
        ->execute();
      foreach ($result as $row) {
        $this->userNodeIds[$row->uid][] = $row->entity_id;
      }
    }

    return $this->userNodeIds[$user->id()] ?? [];
  }

  /**
   * Map from user id to nodes ids.
   *
   * @var array
   */
  private $userTaxonomyTermIds;

  /**
   * Get ids of taxonomy terms a user subscribes to.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user.
   *
   * @return int[]
   *   The taxonomy term ids.
   */
  private function getSubscribedTaxonomyTermIds(User $user): array {
    if (NULL === $this->userTaxonomyTermIds) {
      $result = $this->database
        ->select('flagging', 'f')
        ->fields('f', ['uid', 'entity_id'])
        ->condition('flag_id', 'os2loop_subscription_term')
        ->execute();
      foreach ($result as $row) {
        $this->userTaxonomyTermIds[$row->uid][] = $row->entity_id;
      }
    }

    return $this->userTaxonomyTermIds[$user->id()] ?? [];
  }

  /**
   * Get node from a message.
   *
   * @param \Drupal\message\Entity\Message $message
   *   The message.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node if any.
   */
  private function getMessageNode(Message $message): ?NodeInterface {
    $field = $message->get('os2loop_message_node_refer');
    if (!($field instanceof EntityReferenceFieldItemListInterface)) {
      return NULL;
    }

    $nodes = $field->referencedEntities();

    // @phpstan-ignore-next-line
    return reset($nodes) ?: NULL;
  }

  /**
   * Get user notification interval.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user.
   *
   * @return int
   *   How often the user will receive notifications.
   */
  private function getNotificationInterval(User $user): int {
    return (int) ($user->get(self::USER_NOTIFICATION_INTERVAL_FIELD_NAME)->getValue()[0]['value'] ?: 0);
  }

  /**
   * Get last run at from state.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The time.
   */
  private function getLastRunAt(): DrupalDateTime {
    return $this->getStateValue(self::STATE_LAST_RUN_AT, new DrupalDateTime('@0'));
  }

  /**
   * Set last run at in state.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $time
   *   The time.
   */
  private function setLastRunAt(DrupalDateTime $time) {
    $this->setStateValue(self::STATE_LAST_RUN_AT, $time);
  }

  /**
   * Get module state value.
   *
   * @param string $key
   *   The key.
   * @param mixed $defaultValue
   *   The default value.
   *
   * @return mixed
   *   The state value if any.
   */
  private function getStateValue(string $key, $defaultValue = NULL) {
    $value = $this->state->get(static::MODULE);
    if (!is_array($value)) {
      $value = [];
    }

    return $value[$key] ?? $defaultValue;
  }

  /**
   * Set module state value.
   *
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   */
  private function setStateValue(string $key, $value) {
    $stateValue = $this->state->get(static::MODULE);
    $stateValue[$key] = $value;
    $this->state->set(static::MODULE, $stateValue);
  }

}
