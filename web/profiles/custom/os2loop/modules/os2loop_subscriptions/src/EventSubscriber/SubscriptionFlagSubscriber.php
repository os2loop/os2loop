<?php

namespace Drupal\os2loop_subscriptions\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\flag\FlagInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\views\Views;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for flagging content or terms with subscribe.
 */
class SubscriptionFlagSubscriber implements EventSubscriberInterface {

  /**
   * The logged in user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Helper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Logged in user account.
   * @param \Drupal\flag\FlagServiceInterface $flagService
   *   The flag service.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser, FlagServiceInterface $flagService) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->flagService = $flagService;
  }

  /**
   * Event callback when an entity is flagged.
   *
   * @param \Drupal\flag\Event\FlaggingEvent $event
   *   A flagging event.
   */
  public function onFlag(FlaggingEvent $event) {
    $flag_id = $event->getFlagging()->getFlagId();
    if ('os2loop_subscription_node' === $flag_id || 'os2loop_subscription_term' === $flag_id) {
      // The flagged entity.
      $entity_id = $event->getFlagging()->getFlaggable()->id();
      // The message red flag.
      $flag = $this->flagService->getFlagById('message_read');
      // A list of messages currently displayed to the user @ /user/messages.
      $active_messages = $this->getActiveMessages();

      if ('os2loop_subscription_node' === $flag_id) {
        $this->flagMessageRead($entity_id, $active_messages, $flag);
      }

      if ('os2loop_subscription_term' === $flag_id) {
        $nodes = $this->getReferencedNodes($entity_id);
        foreach ($nodes as $nid => $node) {
          $this->flagMessageRead($nid, $active_messages, $flag);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[FlagEvents::ENTITY_FLAGGED][] = ['onFlag'];
    return $events;
  }

  /**
   * Get all messages that reference a specific node.
   *
   * @return array
   *   A list of messages related to node.
   */
  private function getReferencedMessages(string $nid): array {
    return $this->entityTypeManager
      ->getListBuilder('message')
      ->getStorage()
      ->loadByProperties([
        'os2loop_message_node_refer' => $nid,
      ]);
  }

  /**
   * Get all nodes that reference a specific term from subject or profession.
   *
   * @return array
   *   A list of nodes related to a term.
   */
  private function getReferencedNodes(string $tid): array {
    $subject_reference = $this->entityTypeManager
      ->getListBuilder('node')
      ->getStorage()
      ->loadByProperties([
        'os2loop_shared_subject' => $tid,
      ]);

    $profession_reference = $this->entityTypeManager
      ->getListBuilder('node')
      ->getStorage()
      ->loadByProperties([
        'os2loop_shared_profession' => $tid,
      ]);

    return $subject_reference + $profession_reference;
  }

  /**
   * Get all active messages.
   *
   * Get all messages that the current user has not read from the
   * os2loop_messages view.
   *
   * @return array
   *   A list of message ids of messages that the user has not yet read.
   */
  private function getActiveMessages(): array {
    $message_ids = [];
    $view = Views::getView('os2loop_messages');
    $view->execute();
    foreach ($view->result as $row) {
      $message_ids[] = $row->_entity->id();
    }
    return $message_ids;
  }

  /**
   * Flag a message as being read.
   *
   * @param int $entity_id
   *   A node id.
   * @param array $active_messages
   *   List of messages currently displayed to the user.
   * @param \Drupal\flag\FlagInterface|null $flag
   *   The flag we want to set.
   */
  private function flagMessageRead(int $entity_id, array $active_messages, FlagInterface $flag = NULL) {
    $messages = $this->getReferencedMessages($entity_id);
    foreach ($messages as $id => $message) {
      // Check if the flag has already been set.
      if ($this->currentUser->isAuthenticated()) {
        $entity_message_read = $this->flagService->getFlagging($flag, $message, $this->currentUser);
        if (!$entity_message_read && in_array($id, $active_messages)) {
          $this->flagService->flag($flag, $message, $this->currentUser);
        }
      }
    }
  }

}
