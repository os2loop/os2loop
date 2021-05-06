<?php

namespace Drupal\os2loop_share_with_a_friend\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * MailHelper for creating mail templates.
 */
class MailHelper {
  use StringTranslationTrait;

  /**
   * Implements hook_mail().
   */
  public function mail($key, &$message, $params) {
    $options = [
      'langcode' => $message['langcode'],
    ];
    switch ($key) {
      case 'share_with_a_friend':

        $messageString = $this->t('Someone shared the following content with you: @node_title with the following message', [
          '@node_title' => $params['node_title'],
          ':url' => $params['url'],
        ], $options);
        $subject = $this->t('Someone shared content with you!', $options);
        $message['subject'] = $subject;
        $message['body'][] = $messageString;
        $message['body'][] = $params['message'];
        break;
    }
  }

}
