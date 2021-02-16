# OS2Loop User

## Fixtures

```sh
vendor/bin/drush --yes pm:enable os2loop_user_fixtures
vendor/bin/drush --yes content-fixtures:load --groups=os2loop_user
vendor/bin/drush --yes pm:uninstall content_fixtures
```
