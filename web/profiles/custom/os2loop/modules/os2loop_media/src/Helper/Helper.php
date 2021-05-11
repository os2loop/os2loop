<?php

namespace Drupal\os2loop_media\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;

/**
 * The helper.
 */
class Helper {
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs an flag content form.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Change query for media library.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view related to the alteration.
   * @param \Drupal\views\Plugin\views\query\QueryPluginBase $query
   *   The query to alter.
   */
  public function queryAlter(ViewExecutable $view, QueryPluginBase $query) {
    if ('media_library' === $view->id()) {
      // Add condition if user does not have permission to view all files.
      if (!$this->currentUser->hasPermission('view all files in media browser')) {
        // $groupId = $query->setWhereGroup();
        // @todo The addWhere method below works, but fails coding standards check due to not exist in QueryPluginBase.
        // Suggestions are welcome.
        // $query->addWhere($groupId, 'uid', $this->currentUser->id(), '=');
      }
    }
  }

}
