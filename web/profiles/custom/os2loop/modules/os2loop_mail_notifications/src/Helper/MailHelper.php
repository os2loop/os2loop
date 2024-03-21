<?php

namespace Drupal\os2loop_mail_notifications\Helper;

use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\message\Entity\Message;
use Drupal\os2loop_mail_notifications\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
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
   * Helper constructor.
   */
  public function __construct(
    Settings $settings,
    private readonly Token $token,
    private readonly MailManagerInterface $mailer,
    private readonly LanguageDefault $defaultLanguage
  ) {
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
  }

  /**
   * Implements hook_mail().
   *
   * Prepare a notification mail to be sent.
   */
  public function mail($key, &$message, $params) {
    switch ($key) {
      case self::NOTIFICATION_MAIL:
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
    $langcode = $this->defaultLanguage->get()->getId();

    $sections = [];
    foreach ($groupedMessages as $type => $messages) {
      $section = array_map(function (Message $message) use ($langcode) {
        return $this->getMessageContent($message, $langcode);
      }, $messages);
      $section = implode(PHP_EOL, $section);
      $sections[$type] = $section;
      $params[$type] = $section;
    }

    // Group messages under headings.
    //
    // A message on the form
    //
    //   Something new: <a href="…">New stuff</a> (Revision message: Updated
    //   stuff.)
    //
    // will be put under the heading 'Something new' (colon and space are
    // removed) with content
    //
    //   <a href="…">New stuff</a> (Revision message: Updated
    //   stuff.)
    $messageSections = [];
    foreach ($groupedMessages as $messages) {
      foreach ($messages as $message) {
        $content = $this->getMessageContent($message, $langcode);
        // Use text before colon as heading.
        $parts = preg_split('/:\s*/', $content, 2);
        if (2 === count($parts)) {
          [$heading, $content] = array_map('trim', $parts);
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

    $result = $this->mailer->mail(Helper::MODULE, self::NOTIFICATION_MAIL, $user->getEmail(), $langcode, $params, NULL, TRUE);

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

  /**
   * Get content of a message including any revision message.
   */
  private function getMessageContent(Message $message, string $langCode): string {
    $content = $message->getText($langCode)[0] ?? '';

    // Append any revision message.
    if ($message->hasField('os2loop_revision_message') && !empty($message->get('os2loop_revision_message')->getValue())) {
      $revisionMessage = $this->t(
        'Revision message: @revision_message',
        ['@revision_message' => $message->get('os2loop_revision_message')->getString()],
        ['langcode' => $langCode]
      );
      $content .= ' (' . $revisionMessage . ')';
    }

    return $content;
  }

}
