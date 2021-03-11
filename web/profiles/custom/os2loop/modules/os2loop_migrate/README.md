# OS2Loop Migrate

```sh
vendor/bin/drush pm:enable os2loop_migrate
```

<https://www.drupal.org/docs/upgrading-drupal/upgrade-using-drush>

<https://www.drupal.org/docs/upgrading-drupal/customize-migrations-when-upgrading-to-drupal-8-or-later>

```sh
composer require --dev drupal/migrate_tools drupal/migrate_upgrade
vendor/bin/drush pm:enable migrate_upgrade
# Define the source database with key "migrate"
# https://www.drupal.org/docs/upgrading-drupal/upgrade-using-drush#s-define-the-source-database
vendor/bin/drush migrate:upgrade --configure-only --legacy-db-key=migrate
vendor/bin/drush migrate:status --tag="Drupal 7"

# Migrate users
vendor/bin/drush migrate:import --execute-dependencies d7_user
# Migrate content
vendor/bin/drush migrate:import --execute-dependencies d7_node_complete:external_sources
```
