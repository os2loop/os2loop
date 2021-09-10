<?php

namespace Drupal\os2loop_user\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

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
   * Implements hook_form_alter().
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    switch ($form_id) {
      case 'user_form':
        // Allow only user 1 and administrator and user_administrator to edit
        // user mail.
        $form['account']['mail']['#access'] = 1 === (int) $this->currentUser->id()
          || !empty(array_intersect(
            [
              'os2loop_user_administrator',
              'os2loop_user_user_administrator',
            ],
            $this->currentUser->getRoles()
          ));
        break;
    }
  }

}
