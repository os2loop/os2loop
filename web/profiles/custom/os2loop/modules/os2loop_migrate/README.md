# OS2Loop Migrate

<https://www.drupal.org/docs/upgrading-drupal/upgrade-using-drush>

<https://www.drupal.org/docs/upgrading-drupal/customize-migrations-when-upgrading-to-drupal-8-or-later>

Define the source database with key `migrate` (cf.
<https://www.drupal.org/docs/upgrading-drupal/upgrade-using-drush#s-define-the-source-database>):

```php
$databases['migrate']['default'] = [
  'database' => 'db',
  'username' => 'db',
  'password' => 'db',
  'prefix' => '',
  'host' => '0.0.0.0',
  'port' => '55008',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];
```

Install the OS2Loop Migrate module:

```sh
vendor/bin/drush pm:enable os2loop_migrate
```

```sh
vendor/bin/drush --yes pm:enable config
vendor/bin/drush --yes config:import --partial --source=profiles/custom/os2loop/modules/os2loop_migrate/config/install
vendor/bin/drush --yes pm:uninstall config
```

## Taxonomies

```sh
# We import terms into existing vocabularies
# vendor/bin/drush migrate:import upgrade_d7_taxonomy_vocabulary
vendor/bin/drush migrate:import upgrade_d7_taxonomy_term_subject
vendor/bin/drush migrate:import upgrade_d7_taxonomy_term_keyword
vendor/bin/drush migrate:import upgrade_d7_taxonomy_term_profession
```

## Users

@todo

```sh
vendor/bin/drush migrate:import upgrade_d7_field
vendor/bin/drush migrate:import upgrade_d7_field_instance
vendor/bin/drush migrate:import upgrade_d7_user_role,upgrade_d7_user
```

## Nodes

```sh
vendor/bin/drush migrate:import upgrade_d7_node_complete_page
vendor/bin/drush migrate:import upgrade_d7_node_complete_external_sources
vendor/bin/drush migrate:import upgrade_d7_node_complete_post
```

## Tips and tricks

If a migration gets stuck:

```sh
vendor/bin/drush migrate:reset-status
```

Reverting a migration:

```sh
vendor/bin/drush migrate:roolback upgrade_d7_taxonomy_vocabulary
```

Migration status:

```sh
vendor/bin/drush migrate:status --tag="Drupal 7"
```

```sh
composer require --dev drupal/migrate_tools drupal/migrate_upgrade
vendor/bin/drush pm:enable migrate_upgrade
vendor/bin/drush migrate:upgrade --configure-only --legacy-db-key=migrate
vendor/bin/drush migrate:status --tag="Drupal 7"

# Migrate users
vendor/bin/drush migrate:import --execute-dependencies d7_user
# Migrate content
vendor/bin/drush migrate:import --execute-dependencies d7_node_complete:external_sources
```
