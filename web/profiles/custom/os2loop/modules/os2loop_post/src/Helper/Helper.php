<?php

namespace Drupal\os2loop_post\Helper;

use Drupal\Core\Form\FormStateInterface;

/**
 * Helper for posts.
 */
class Helper {

  /**
   * Implements hook_form_alter().
   *
   * Alter forms related to posts.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    switch ($form_id) {
      case 'comment_os2loop_post_comment_form':
        $this->hidePreviewButton($form, $form_state, $form_id);
        break;
    }
  }

  /**
   * Hide preview button in form.
   *
   * @param array $form
   *   The form being altered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   * @param string $form_id
   *   The id of the the form.
   */
  private function hidePreviewButton(array &$form, FormStateInterface $form_state, string $form_id) {
    $form['actions']['preview']['#access'] = FALSE;
  }

}
