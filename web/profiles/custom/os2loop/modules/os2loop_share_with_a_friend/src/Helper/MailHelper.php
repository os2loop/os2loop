<?php

namespace Drupal\os2loop_share_with_a_friend\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;
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
    switch ($key) {
      case 'share_with_a_friend':
        $node = $params['node'];
        // @todo move this to settings
        $template = '[current-user:name] shared the following with you: [node:title] ([node:url]) with the following message: [os2loop_share_with_a_friend:message]';
        $data['node'] = $node;
        $data['message'] = $params['message'];
        $body = $this->renderTemplate($template, $data);
        $template = '[current-user:name] wants to share content from [site:name] with you';
        $subject = $this->renderTemplate($template);
        $message['subject'] = $subject;
        $message['body'][] = $body;
        break;
    }
  }

  /**
   * Renders content of a mail.
   */
  public function renderTemplate($template, array $data = NULL) {
    if (isset($data)) {
      return $this->token->replace($template, [
        'node' => $data['node'],
        'message' => $data['message'],
      ], []);
    }
    else {
      return $this->token->replace($template, [], []);
    }
  }

  /**
   * Implements hook_tokens().
   */
  public function tokens($type, $tokens, array $data) {
    $replacements = [];
    if ($type == 'os2loop_share_with_a_friend' && !empty($data['message'])) {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'message':
            $replacements[$original] = $data['message'];
            break;
        }
      }
    }
    return $replacements;
  }

  /**
   * Implements hook_token_info().
   */
  public function tokenInfo() {
    $types['os2loop_share_with_a_friend'] = [
      'name' => $this->t('Message type'),
    ];
    $tokens['message'] = [
      'name' => $this->t('Message'),
    ];

    return [
      'types' => $types,
      'tokens' => [
        'os2loop_share_with_a_friend' => $tokens,
      ],
    ];

  }

}
