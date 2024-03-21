<?php

namespace Drupal\os2loop_user\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ViewExecutable;

/**
 * The Helper class.
 */
class Helper {
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * Constructor.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Implements hook_views_query_alter().
   */
  public function queryAlter(ViewExecutable $view, QueryPluginBase $query) {
    if ($view->id() === 'os2loop_user_answers') {
      $this->sqlQueryAlter($view, $query);
    }
  }

  /**
   * Implements hook_form_alter().
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    // Allow only user 1 and administrator and user_administrator to edit
    // user mail.
    if ($form_id == 'user_form') {
      $form['account']['mail']['#access'] = 1 === (int) $this->currentUser->id()
        || !empty(array_intersect(
          [
            'os2loop_user_administrator',
            'os2loop_user_user_administrator',
          ],
          $this->currentUser->getRoles()
        ));
    }
  }

  /**
   * Helper function for SQL queries.
   *
   * Change the order of the tables in a LEFT JOIN to preserve NULLs from
   * content type.
   */
  private function sqlQueryAlter(ViewExecutable $view, Sql $query) {
    $node_table = $query->getTableInfo('node_field_data_comment_field_data');

    if (isset($node_table) && $node_table['join']->type === 'LEFT') {
      $node_table['join']->type = 'RIGHT';
      $query->where[0]['type'] = 'OR';
      $query->addWhereExpression(0, 'comment_field_data.uid IS NULL');
    }
  }

}
