# OS2Loop Mail notifications

Sends mail notifications to users when content the subscribe to has been created
or edited.

A cron task is run daily to send out notifications.

## Force run

```php
# Reset state and user data and run cron.
vendor/bin/drush php-eval "Drupal::state()->set('os2loop_mail_notifications', []); Drupal::service('user.data')->delete('os2loop_mail_notifications'); Drupal::service('os2loop_mail_notifications.helper')->cron()"
```
