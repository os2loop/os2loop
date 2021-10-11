# OS2Loop Alert

## SMTP

To use [SMTP Authentication Support](https://www.drupal.org/project/smtp), you
have to enable it in `settings.local.php`:

```php
// Requires settings
$config['system.mail']['interface']['default'] = 'SMTPMailSystem';
$config['smtp.settings']['smtp_on'] = true;
// Optional settings
// $config['smtp.settings']['smtp_host'] = '127.0.0.1';
// $config['smtp.settings']['smtp_port'] = '25';

// $config['smtp.settings']['smtp_debugging'] = true;

```
