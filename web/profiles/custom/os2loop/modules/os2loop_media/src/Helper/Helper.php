<?php

namespace Drupal\os2loop_media\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\Plugin\views\query\Sql;
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
   * Implements hook_views_query_alter().
   *
   * Change query for media library.
   * The view config sets condition for viewing only media created by users with
   * more than the authenticated role.
   *
   * @todo Move the role condition into this alter query and add a specific permission for viewing media added by users with multple roles.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view.
   * @param \Drupal\views\Plugin\views\query\QueryPluginBase $query
   *   The query.
   */
  public function queryAlter(ViewExecutable $view, QueryPluginBase $query) {
    if ($query instanceof Sql && ('media_library' === $view->id()) || ('media' === $view->id())) {
      $currentUserRoles = $this->currentUser->getRoles();

      // Allow display of all files for users with permission.
      if ($this->currentUser->hasPermission('view all files in media browser')) {
        $query = $this->removeRoleRestriction($query);
      }

      // Show only own files for users that are just authenticated
      // (but not user 1).
      if (1 === count($currentUserRoles) && in_array('authenticated', $currentUserRoles) && '1' !== $this->currentUser->id()) {
        $groupId = $query->setWhereGroup();
        // @phpstan-ignore-next-line
        $query->addWhere($groupId, 'media_field_data.uid', $this->currentUser->id(), '=');
        $query = $this->removeRoleRestriction($query);
      }
    }
  }

  /**
   * Remove role restriction from query.
   *
   * @param \Drupal\views\Plugin\views\query\QueryPluginBase $query
   *   The query.
   *
   * @property mixed $where
   *
   * @return \Drupal\views\Plugin\views\query\QueryPluginBase
   *   The modified query.
   */
  private function removeRoleRestriction(QueryPluginBase $query): QueryPluginBase {
    // Remove role restriction.
    // @phpstan-ignore-next-line
    foreach ($query->where[1]['conditions'] as $key => $condition) {
      if ('users_field_data_media_field_data__user__roles.roles_target_id' === $condition['field']) {
        // @phpstan-ignore-next-line
        unset($query->where[1]['conditions'][$key]);
      }
    }

    return $query;
  }

  /**
   * Implements hook_form_alter().
   *
   * Alter media add/edit forms.
   * Deny access to field_media_library for users that are only authenticated,
   * make field required for users with more than authenticated users.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    switch ($form_id) {
      case 'media_os2loop_media_file_add_form':
      case 'media_os2loop_media_image_add_form':
      case 'media_os2loop_media_file_edit_form':
      case 'media_os2loop_media_image_edit_form':
      case 'media_library_add_form_upload':
        $currentUserRoles = $this->currentUser->getRoles();
        if (1 < count($currentUserRoles) || '1' == $this->currentUser->id()) {
          $form['field_media_library']['widget']['#required'] = TRUE;
        }
        else {
          $form['field_media_library']['#access'] = FALSE;
        }
        break;
    }

    // The inline upload form is built later so we use after build.
    if ('media_library_add_form_upload' === $form_id) {
      $form['#after_build'][] = [$this, 'afterBuild'];
    }
  }

  /**
   * Modify inline upload form.
   *
   * Deny access to field_media_library for users that are only authenticated,
   * make field required for users with more than authenticated users.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   *   The altered form.
   */
  public function afterBuild(array $form, FormStateInterface $form_state) {
    if (!empty($form['media'][0]['fields']['field_media_library'])) {
      $currentUserRoles = $this->currentUser->getRoles();
      if (1 < count($currentUserRoles) || '1' == $this->currentUser->id()) {
        $form['media'][0]['fields']['field_media_library']['widget']['#required'] = TRUE;
      }
      else {
        $form['media'][0]['fields']['field_media_library']['#access'] = FALSE;
      }
    }

    return $form;
  }

}
