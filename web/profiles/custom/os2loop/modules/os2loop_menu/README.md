# OS2Loop Menu

Adds default menu items to main menu on install.

## Fixtures

```sh
vendor/bin/drush --yes pm:enable os2loop_menu
vendor/bin/drush --yes content-fixtures:load --groups=os2loop_menu,os2loop_section_page
vendor/bin/drush --yes pm:uninstall content_fixtures
```
