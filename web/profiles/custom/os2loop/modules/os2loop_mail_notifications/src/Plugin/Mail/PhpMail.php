<?php

namespace Drupal\os2loop_mail_notifications\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Mail\Plugin\Mail\PhpMail as PhpMailBase;
use Drupal\Core\Url;

/**
 * Copy of the default Drupal mail backend, using PHP's native mail() function.
 *
 * @Mail(
 *   id = "os2loop_mail_notifications",
 *   label = @Translation("Custom PHP mailer"),
 *   description = @Translation("Sends the message as plain text, using PHP's native mail() function.")
 * )
 */
class PhpMail extends PhpMailBase implements MailInterface {

  /**
   * Concatenates and wraps the email body for plain-text mails.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return array
   *   The formatted $message.
   */
  public function format(array $message) {
    $message['body'] = implode("\n\n", $message['body']);

    $message['body'] = $this->makeUrlsAbsolute($message['body']);

    return $message;
  }

  /**
   * Sends an email message.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return bool
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   *
   * @see http://php.net/manual/function.mail.php
   * @see \Drupal\Core\Mail\MailManagerInterface::mail()
   */
  public function mail(array $message) {
    $message['headers']['Content-Type'] = 'text/html; charset=UTF-8;';
    return parent::mail($message);
  }

  /**
   * Convert relative urls (paths) in html to absolute urls.
   */
  private function makeUrlsAbsolute(string $html) {
    return preg_replace_callback(
      '/\s(href|src)\s*=\s*(?:\'([^\']+)\'|"([^"]+)")/',
      static function ($matches) {
        $name = $matches[1];
        $value = $matches[2] ?: $matches[3];
        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (NULL === $scheme) {
          // We have a local path.
          try {
            $value = Url::fromUri('internal:/' . ltrim($value, '/'), ['absolute' => TRUE])->toString();
          }
          catch (\InvalidArgumentException $exception) {
          }
        }

        return sprintf(' %s="%s"', $name, htmlspecialchars($value));
      },
      $html
    );
  }

}
