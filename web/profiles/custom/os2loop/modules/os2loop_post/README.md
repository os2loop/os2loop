# OS2Loop Post

## Fixtures

```sh
vendor/bin/drush --yes pm:enable os2loop_post_fixtures
vendor/bin/drush --yes content-fixtures:load \
  --groups=os2loop_post,os2loop_file,os2loop_taxonomy
vendor/bin/drush --yes pm:uninstall content_fixtures
```
