<?php

namespace Drupal\os2loop_messages\Helper;

use Drupal\comment\CommentInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\message\Entity\Message;
use Drupal\node\NodeInterface;

/**
 * Os2Loop messages helper.
 *
 * Helper class for creating messages.
 */
class Helper extends ControllerBase {

  /**
   * Implements hook_entity_insert().
   *
   * Create message on insert entity.
   */
  public function entityInsert(EntityInterface $entity) {
    $this->createMessage($entity, 'ins');
  }

  /**
   * Implements hook_entity_update().
   *
   * Create message on update entity.
   */
  public function entityUpdate(EntityInterface $entity) {
    $this->createMessage($entity, 'upd');
  }

  /**
   * Create a message.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being to relate to.
   * @param string $operation
   *   The operation performed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createMessage(EntityInterface $entity, string $operation) {
    $template = $this->getMessageTemplate($entity, $operation);
    if (NULL !== $template) {
      $message = Message::create(['template' => $template]);
      if ($entity instanceof NodeInterface) {
        $message->set('os2loop_message_node_refer', $entity);
      }
      elseif ($entity instanceof CommentInterface) {
        $node_storage = $this->entityTypeManager()->getStorage('node');
        $node = $node_storage->load($entity->get('entity_id')->getValue()[0]['target_id']);
        $message->set('os2loop_message_node_refer', $node);
        $message->set('os2loop_message_comment_refer', $entity);
      }
      $message->save();
    }
  }

  /**
   * Map from bundle to message type.
   *
   * @var array
   */
  private const BUNDLE_MESSAGE_TYPES = [
    'os2loop_documents_collection' => 'os2loop_message_collection',
    'os2loop_documents_document' => 'os2loop_message_document',
    'os2loop_post' => 'os2loop_message_post',
    'os2loop_post_comment' => 'os2loop_message_comment',
    'os2loop_question' => 'os2loop_message_question',
    'os2loop_question_answer' => 'os2loop_message_answer',
  ];

  /**
   * Get message template for an entity operation.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $operation
   *   The operation ('ins' or 'upd').
   *
   * @return null|string
   *   The message template name if any.
   */
  private function getMessageTemplate(EntityInterface $entity, string $operation) {
    $template = self::BUNDLE_MESSAGE_TYPES[$entity->bundle()] ?? NULL;
    if (NULL !== $template) {
      $template .= '_' . $operation;
    }

    return $template;
  }

}
