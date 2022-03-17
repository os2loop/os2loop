<?php

namespace Drupal\os2loop_revision_date\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Session\AccountInterface;

/**
 * Sets revision date on content.
 *
 * @Action(
 *   id = "os2loop_revision_date_set_action",
 *   label = @Translation("Set revision date"),
 *   type = "node"
 * )
 */
class SetRevisionDate extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    if ($node) {
      /** @var \Drupal\node\NodeInterface $node */
      if (!$node->hasField('os2loop_shared_rev_date')) {
        throw new \RuntimeException("Revisioning date field not found on node.");
      }
      $node->set('os2loop_shared_rev_date', $this->getNextRevisionDate()->format('Y-m-d'));
      $node->save();
    }
  }

  /**
   * Get datetime from action configuration yml.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The next time the node should be revisioned.
   */
  private function getNextRevisionDate(): DrupalDateTime {
    $extend = $this->configuration['time_span'] ?? 'now';
    return new DrupalDateTime($extend, date_default_timezone_get());
  }

}
