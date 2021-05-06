<?php

namespace Drupal\os2loop_flag_content\Helper;

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
      case 'flag_content':
        $node = $params['node'];
        // @todo move this to settings
        $template = '[current-user:name] flagged the following content: [node:title]([node:url]) with the reason [reason] and the following message';
        $template = str_replace('[reason]', $params['reason'], $template);
        $bubbleable_metadata = new BubbleableMetadata();
        $build['#markup'] = $this->token->replace($template, ['node' => $node], [], $bubbleable_metadata);
        $bubbleable_metadata->applyTo($build);
        $message['subject'] = $this->t('Flagged content', $options);
        $message['body'][] = $build['#markup'];
        $message['body'][] = $params['message'];
        break;
    }
  }

}
