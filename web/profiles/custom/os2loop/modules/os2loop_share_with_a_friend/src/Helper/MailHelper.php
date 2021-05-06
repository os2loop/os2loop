<?php

namespace Drupal\os2loop_share_with_a_friend\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Utility\Token;

/**
 * MailHelper for creating mail templates.
 */
class MailHelper {
  use StringTranslationTrait;
  /**
   * The toke.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token.
   */
  public function __construct(Token $token) {
    $this->token = $token;
  }

  /**
   * Implements hook_mail().
   */
  public function mail($key, &$message, $params) {
    $options = [
      'langcode' => $message['langcode'],
    ];
    switch ($key) {
      case 'share_with_a_friend':
        $node = $params['node'];
        // @todo move this to settings
        $template = '[current-user:name] shared the following with you: [node:title]([node:url]) with the following message';
        $bubbleable_metadata = new BubbleableMetadata();
        $build['#markup'] = $this->token->replace($template, ['node' => $node], [], $bubbleable_metadata);
        $bubbleable_metadata->applyTo($build);

        $subject = $this->t('Someone shared content with you!', $options);
        $message['subject'] = $subject;
        $message['body'][] = $build['#markup'];
        $message['body'][] = $params['message'];
        break;
    }
  }

}
