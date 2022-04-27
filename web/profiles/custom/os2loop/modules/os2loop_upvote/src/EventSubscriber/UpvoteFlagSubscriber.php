<?php

namespace Drupal\os2loop_upvote\EventSubscriber;

use Drupal\comment\Entity\Comment;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Event subscriber for flagging content or terms with subscribe.
 */
class UpvoteFlagSubscriber implements EventSubscriberInterface {
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
   * @param \Drupal\flag\FlagServiceInterface $flagService
   *   The flag service.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FlagServiceInterface $flagService) {
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
    $flag_type_id = $event->getFlagging()->getFlagId();
    if ('os2loop_upvote_correct_answer' === $flag_type_id) {
      // The flagged entity.
      $entity = $event->getFlagging()->getFlaggable();
      if ('comment' === $entity->getEntityTypeId()) {
        /** @var \Drupal\comment\CommentInterface $entity */
        $node = $entity->getCommentedEntity();
        $node_comments = $this->getNodeComments($node->id());
        foreach ($node_comments as $comment) {
          if ($comment->id() !== $entity->id()) {
            // Unflag comment.
            $this->unflagComment($flag_type_id, $comment);
          }
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
   * Get all comments related to a node.
   *
   * @param string $nid
   *   The Id of a node.
   *
   * @return array
   *   A list of comments.
   */
  private function getNodeComments(string $nid): array {
    try {
      $comment_ids = $this->entityTypeManager
        ->getStorage('comment')
        ->getQuery('AND')
        ->condition('entity_id', $nid)
        ->condition('entity_type', 'node')
        ->execute();

      return $this->entityTypeManager
        ->getStorage('comment')
        ->loadMultiple($comment_ids);
    }

    catch (\Exception $exception) {
      return [];
    }
  }

  /**
   * Unflag a comment if a flag exists.
   *
   * @param string $flag_type_id
   *   The flag type.
   * @param \Drupal\comment\Entity\Comment $comment
   *   The comment to unflag.
   */
  private function unflagComment(string $flag_type_id, Comment $comment) {
    $flag = $this->flagService->getFlagById($flag_type_id);
    if ($this->flagService->getFlagging($flag, $comment)) {
      $this->flagService->unflag($flag, $comment);
    }
  }

}
