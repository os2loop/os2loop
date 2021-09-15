<?php

namespace Drupal\os2loop_mail_notifications\Helper;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\message\Entity\Message;
use Drupal\os2loop_mail_notifications\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\Core\Utility\Token;
use Drupal\user\Entity\User;

/**
 * OS2Loop Mail notifications mail helper.
 */
class MailHelper {
  use StringTranslationTrait;

  private const NOTIFICATION_MAIL = 'os2loop_mail_notifications_notification';

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The token.
   *
   * @var \Drupal\Core\Utility\Token
   */
  private $token;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private $mailer;

  /**
   * Helper constructor.
   */
  public function __construct(Settings $settings, Token $token, MailManagerInterface $mailer) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
    $this->token = $token;
    $this->mailer = $mailer;
  }

  /**
   * Implements hook_mail().
   *
   * Prepare a notification mail to be sent.
   */
  public function mail($key, &$message, $params) {
    switch ($key) {
      case static::NOTIFICATION_MAIL:
        $body_template = $this->config->get('template_body');
        $subject_template = $this->config->get('template_subject');
        $data = [
          'user' => $params['user'],
          'os2loop_mail_notifications' => [
            // Prevent html escaping by converting to markup.
            'messages' => Markup::create($params['messages']),
            'messages_with_headings' => Markup::create($params['messages_with_headings']),
          ],
        ];
        $message['subject'] = $this->renderTemplate($subject_template, $data);
        $message['body'][] = $this->renderTemplate($body_template, $data);
        break;
    }
  }

  /**
   * Send notification.
   *
   * @return bool
   *   True if mail is sent.
   */
  public function sendNotification(User $user, array $groupedMessages) {
    $lang_code = $user->getPreferredLangcode();

    $sections = [];
    foreach ($groupedMessages as $type => $messages) {
      $section = array_map(static function (Message $message) use ($lang_code) {
        return $message->getText($lang_code)[0] ?? NULL;
      }, $messages);
      $section = implode(PHP_EOL, $section);
      $sections[$type] = $section;
      $params[$type] = $section;
    }

    // Group messages under headings.
    //
    // A message on the form
    //
    //   Something new: <a href="…">New stuff</a>.
    //
    // will be put under the heading "Something new" (colon and space are
    // removed) as <a href="…">New stuff</a> (only a element is used).
    $messageSections = [];
    foreach ($groupedMessages as $messages) {
      foreach ($messages as $message) {
        $text = $message->getText($lang_code)[0] ?? NULL;
        // Use text before a element as heading and keep only the a element as
        // text.
        if (NULL !== $text
        && preg_match('@^(?P<heading>[^<]+?)(:\s*)?(?P<content><a.+</a>)@', $text, $matches)) {
          [$heading, $content] = [$matches['heading'], $matches['content']];
          $messageSections[$heading][] = $content;
        }
      }
    }
    $messagesWithHeadings = '';
    foreach ($messageSections as $heading => $content) {
      $messagesWithHeadings .= $heading . PHP_EOL . PHP_EOL . '* ' . implode(PHP_EOL . '* ', $content) . PHP_EOL . PHP_EOL;
    }
    $params['messages_with_headings'] = $messagesWithHeadings;

    $sections = array_filter($sections);
    $params['messages'] = implode(PHP_EOL . PHP_EOL, $sections);
    $params['user'] = $user;

    $result = $this->mailer->mail(Helper::MODULE, static::NOTIFICATION_MAIL, $user->getEmail(), $lang_code, $params, NULL, TRUE);

    return TRUE === $result['result'];
  }

  /**
   * Renders content of a mail.
   */
  public function renderTemplate($template, array $data) {
    return $this->token->replace($template, $data, []);
  }

  /**
   * Implements hook_tokens().
   *
   * Replace tokens related to mail notifications.
   */
  public function tokens($type, $tokens, array $data) {
    $replacements = [];
    if ('os2loop_mail_notifications' === $type && isset($data[$type])) {
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
   *
   * Prepare tokens related to mail notifications.
   */
  public function tokenInfo() {
    return [
      'types' => [
        'os2loop_mail_notifications' => [
          'name' => $this->t('Mail notifications'),
          'description' => $this->t('Tokens related to mail notifications.'),
          'needs-data' => 'os2loop_mail_notifications',
        ],
      ],
      'tokens' => [
        'os2loop_mail_notifications' => [
          'messages' => [
            'name' => $this->t('The messages'),
            'description' => $this->t('The messages.'),
          ],
          'messages_with_headings' => [
            'name' => $this->t('The messages with headings'),
            'description' => $this->t('The messages in sections with headings.'),
          ],
        ],
      ],
    ];
  }

}
