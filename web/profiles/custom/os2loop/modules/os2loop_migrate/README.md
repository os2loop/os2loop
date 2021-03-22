# OS2Loop Migrate

<https://www.drupal.org/docs/upgrading-drupal/upgrade-using-drush>

<https://www.drupal.org/docs/upgrading-drupal/customize-migrations-when-upgrading-to-drupal-8-or-later>

Define the source database with key `migrate` (cf.
<https://www.drupal.org/docs/upgrading-drupal/upgrade-using-drush#s-define-the-source-database>):

"Altering migrations"
(<https://www.lullabot.com/articles/overview-migrating-drupal-sites-8>)

Flags:

<https://deninet.com/tag/building-custom-migration-drupal-8>
<https://www.drupal.org/project/migrate_extras/issues/1794568>
<https://www.drupal.org/node/2503815>
<https://www.drupal.org/project/flag/issues/2409901>

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

## Files

```sh
mkdir -p migrate/sites/default
rsync --archive --compress --delete «old site root»/sites/default/files migrate/sites/default
vendor/bin/drush migrate:import upgrade_d7_file
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

```sh
# Roles are defined in config.
# vendor/bin/drush migrate:import upgrade_d7_user_role
vendor/bin/drush migrate:import upgrade_d7_user
```

## Nodes

```sh
vendor/bin/drush migrate:import upgrade_d7_node_complete_page
vendor/bin/drush migrate:import upgrade_d7_node_complete_external_sources
vendor/bin/drush migrate:import upgrade_d7_node_complete_post
vendor/bin/drush migrate:import upgrade_d7_node_complete_loop_documents_document
vendor/bin/drush migrate:import upgrade_d7_node_complete_loop_documents_collection
```

## Comments

```sh
vendor/bin/drush migrate:import upgrade_d7_comment
```

## Messages

```sh
# Text formats are defined in config.
# vendor/bin/drush migrate:import upgrade_d7_filter_format
# Message templates are defined in config.
# vendor/bin/drush migrate:import upgrade_d7_message_template
vendor/bin/drush migrate:import upgrade_d7_message
```

## Flags

```sh
# Flags are define in config.
# vendor/bin/drush migrate:import upgrade_d7_flag
vendor/bin/drush migrate:import upgrade_d7_flagging
```

```sh
mysqldump --user=db --password=db --host=127.0.0.1 --port=58847 --no-create-info --complete-insert db flagging
```

## Tips and tricks

If a migration gets stuck:

```sh
vendor/bin/drush migrate:reset-status
```

Reverting a migration:

```sh
vendor/bin/drush migrate:rollback
```

Migration status:

```sh
vendor/bin/drush migrate:status --tag="Drupal 7"
```

Migration messages:

```sh
vendor/bin/drush migrate:messages
```

```sh
composer require --dev drupal/migrate_tools drupal/migrate_upgrade
vendor/bin/drush pm:enable migrate_upgrade
vendor/bin/drush migrate:upgrade --configure-only --legacy-db-key=migrate
vendor/bin/drush migrate:status --tag="Drupal 7"

# Migrate users
vendor/bin/drush migrate:import --execute-dependencies upgrade_d7_user
# Migrate content
vendor/bin/drush migrate:import --execute-dependencies upgrade_d7_node_complete:external_sources
```
