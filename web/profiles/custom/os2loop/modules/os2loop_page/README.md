# OS2Loop Page

## Fixtures

```sh
vendor/bin/drush --yes pm:enable os2loop_page_fixtures
vendor/bin/drush --yes content-fixtures:load --groups=os2loop_page,os2loop_file
vendor/bin/drush --yes pm:uninstall content_fixtures
```
