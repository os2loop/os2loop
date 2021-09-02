<?php

namespace Drupal\os2loop_share_with_a_friend\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\os2loop_share_with_a_friend\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;

/**
 * MailHelper for creating mail templates.
 */
class MailHelper {
  use StringTranslationTrait;
  /**
   * The token.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * Constructor.
   */
  public function __construct(Token $token, Settings $settings) {
    $this->token = $token;
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
  }

  /**
   * Implements hook_mail().
   *
   * Prepare mail for sharing with a friend.
   */
  public function mail($key, &$message, $params) {
    switch ($key) {
      case 'share_with_a_friend':
        $body_template = $this->config->get('template_body');
        $subject_template = $this->config->get('template_subject');
        $data = [
          'node' => $params['node'],
          'os2loop_share_with_a_friend' => [
            'message' => $params['message'],
          ],
        ];
        $message['subject'] = $this->renderTemplate($subject_template, $data);
        $message['body'][] = $this->renderTemplate($body_template, $data);
        break;
    }
  }

  /**
   * Renders content of a mail.
   */
  public function renderTemplate($template, array $data) {
    return $this->token->replace($template, $data, []);
  }

  /**
   * Implements hook_tokens().
   */
  public function tokens($type, $tokens, array $data) {
    $replacements = [];
    if ('os2loop_share_with_a_friend' === $type && isset($data[$type])) {
      foreach ($tokens as $name => $original) {
        if (isset($data[$type][$name])) {
          $replacements[$original] = $data[$type][$name];
        }
      }
    }

    return $replacements;
  }

  /**
   * Implements hook_token_info().
   */
  public function tokenInfo() {
    return [
      'types' => [
        'os2loop_share_with_a_friend' => [
          'name' => $this->t('Share with a friend'),
          'description' => $this->t('Tokens related to share with a friend.'),
          'needs-data' => 'os2loop_share_with_a_friend',
        ],
      ],
      'tokens' => [
        'os2loop_share_with_a_friend' => [
          'message' => [
            'name' => $this->t('The message'),
            'description' => $this->t('The message.'),
          ],
        ],
      ],
    ];
  }

}
