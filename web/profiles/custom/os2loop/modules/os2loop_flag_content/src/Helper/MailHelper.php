<?php

namespace Drupal\os2loop_flag_content\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\os2loop_flag_content\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;

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
   */
  public function mail($key, &$message, $params) {
    switch ($key) {
      case 'flag_content':
        $node = $params['node'];
        $body_template = $this->config->get('template_body');
        $subject_template = $this->config->get('template_subject');
        $data['node'] = $node;
        $data['reason'] = $params['reason'];
        $data['message'] = $params['message'];
        $body = $this->renderTemplate($body_template, $data);
        $subject = $this->renderTemplate($subject_template, $data);
        $message['subject'] = $subject;
        $message['body'][] = $body;
        break;
    }
  }

  /**
   * Renders content of a mail.
   */
  public function renderTemplate($template, array $data = NULL) {
    return $this->token->replace($template, [
      'node' => $data['node'],
      'reason' => $data['reason'],
      'message' => $data['message'],
    ], []);

  }

  /**
   * Implements hook_tokens().
   */
  public function tokens($type, $tokens, array $data) {
    $replacements = [];
    if ('os2loop_flag_content' === $type && isset($data[$type])) {
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
        'os2loop_flag_content' => [
          'name' => $this->t('Flag content'),
          'description' => $this->t('Tokens related to flag content.'),
          'needs-data' => 'os2loop_flag_content',
        ],
      ],
      'tokens' => [
        'os2loop_share_with_a_friend' => [
          'message' => [
            'name' => $this->t('The message'),
            'description' => $this->t('The message.'),
          ],
          'reason' => [
            'name' => $this->t('The reason'),
            'description' => $this->t('The reason.'),
          ],
        ],
      ],
    ];
  }

}
