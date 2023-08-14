# OS2Loop Mail notifications

Sends mail notifications to users when content the subscribe to has been created
or edited.

A cron task is run daily to send out notifications.

## Force run

```php
# Reset state and user data and run cron.
docker compose exec phpfpm vendor/bin/drush php:eval "Drupal::state()->set('os2loop_mail_notifications', []); Drupal::service('user.data')->delete('os2loop_mail_notifications'); Drupal::service(Drupal\\os2loop_mail_notifications\\Helper\\Helper::class)->cron()"
```
