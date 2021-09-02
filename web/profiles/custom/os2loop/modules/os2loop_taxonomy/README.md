# OS2Loop Taxonomy

Adds taxonomy terms.

## Fixtures

```sh
vendor/bin/drush --yes pm:enable os2loop_taxonomy_fixtures
vendor/bin/drush --yes content-fixtures:load --groups=os2loop_taxonomy
vendor/bin/drush --yes pm:uninstall content_fixtures
```
