# OS2loop

[![Github](https://img.shields.io/badge/source-os2loop/os2loop-blue?style=flat-square)](https://github.com/os2loop/os2loop)
[![Release](https://img.shields.io/github/v/release/os2loop/os2loop?sort=semver&style=flat-square)](https://github.com/os2loop/os2loop/releases)
[![PHP Version](https://img.shields.io/badge/PHP-%5E7.4-9cf)](https://www.php.net/downloads)
[![Build Status](https://img.shields.io/github/workflow/status/itk-dev/os2loop/PR%20Review?&logo=github&style=flat-square)](https://github.com/os2loop/os2loop/actions?query=workflow%3A%22Test+%26+Code+Style+Review%22)
[![Read License](https://img.shields.io/github/license/os2loop/os2loop)](https://github.com/os2loop/os2loop/blob/master/LICENSE.txt)
[![Github downloads](https://img.shields.io/github/downloads/os2loop/os2loop/total?style=flat-square&colorB=darkmagenta)](https://packagist.org/packages/os2loop/os2loop/stats)

OS2loop is a question-answering system built on Drupal 9. See [os2.eu/produkt/os2loop](https://os2.eu/produkt/os2loop)
(in Danish) for more information.

## Installation

### Production

Create local settings file with database connection:

```sh
cat <<'EOF' > web/sites/default/settings.local.php
<?php
$databases['default']['default'] = [
 'database' => getenv('DATABASE_DATABASE') ?: 'db',
 'username' => getenv('DATABASE_USERNAME') ?: 'db',
 'password' => getenv('DATABASE_PASSWORD') ?: 'db',
 'host' => getenv('DATABASE_HOST') ?: 'mariadb',
 'port' => getenv('DATABASE_PORT') ?: '',
 'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
 'prefix' => '',
];
EOF
```

```sh
composer install --no-dev --optimize-autoloader
vendor/bin/drush --yes site:install os2loop --existing-config
vendor/bin/drush --yes locale:update
```

You must also build the [OS2Loop
theme](web/profiles/custom/os2loop/themes/os2loop_theme/README.md) assets; see
[Building
assets](web/profiles/custom/os2loop/themes/os2loop_theme/README.md#building-assets)
for details.

After installing the site you should check out [Configuring
OS2Loop](docs/configuring-os2loop.md) for important and useful post-install
instructions.

See [OS2Loop modules](docs/modules.md) for a complete list of custom OS2Loop
modules.

### Development

See [docs/development](docs/development/README.md) for details on development.

```sh
docker-compose up --detach
docker-compose exec phpfpm composer install
docker-compose exec phpfpm vendor/bin/drush --yes site:install os2loop --existing-config
# Get the site url
echo "http://$(docker-compose port nginx 80)"
# Get admin sign in url
docker-compose exec phpfpm vendor/bin/drush --yes --uri="http://$(docker-compose port nginx 80)" user:login
```

### Modules

Uses a dev version of views_flag_refresh since the module
is not yet available on drupal.org

#### Mails

Mails are caught by [MailHog](https://github.com/mailhog/MailHog) and can be
read on the url reported by

```sh
echo "http://$(docker-compose port mailhog 8025)"
```

### Fixtures

We have fixtures for all content types.

To load all content type fixtures, run:

```sh
# Find and enable all fixtures modules
docker-compose exec phpfpm vendor/bin/drush --yes pm:enable $(find web/profiles/custom/os2loop/modules/ -type f -name 'os2loop_*_fixtures.info.yml' -exec basename -s .info.yml {} \;)
# Disable "Entity Reference Integrity Enforce" module to be able to delete (purge) content before loading fixtures.
docker-compose exec phpfpm vendor/bin/drush --yes pm:uninstall entity_reference_integrity_enforce
# Load the fixtures
docker-compose exec phpfpm vendor/bin/drush --yes content-fixtures:load
# Uninstall all fixtures modules
docker-compose exec phpfpm vendor/bin/drush --yes pm:uninstall content_fixtures
# Enable "Entity Reference Integrity Enforce" module
docker-compose exec phpfpm vendor/bin/drush --yes pm:enable entity_reference_integrity_enforce
```

## Updates

```sh
composer install --no-dev --optimize-autoloader
vendor/bin/drush --yes updatedb
vendor/bin/drush --yes config:import
vendor/bin/drush --yes locale:update
vendor/bin/drush --yes cache:rebuild
```

## Translations

Import translations by running

```sh
(cd web && ../vendor/bin/drush locale:import --type=customized --override=none da profiles/custom/os2loop/translations/translations.da.po)
```

Export translations by running

```sh
(cd web && ../vendor/bin/drush locale:export da --types=customized > profiles/custom/os2loop/translations/translations.da.po)
```

Open `web/profiles/custom/os2loop/translations/translations.da.po` with the
latest version of [Poedit](https://poedit.net/) to clean up and then save the
file.

See
<https://medium.com/limoengroen/how-to-deploy-drupal-interface-translations-5653294c4af6>
for further details.

## Coding standards

```sh
composer coding-standards-check
composer coding-standards-apply
```

```sh
docker run --volume ${PWD}:/app --workdir /app node:latest yarn install
docker run --volume ${PWD}:/app --workdir /app node:latest yarn coding-standards-check
docker run --volume ${PWD}:/app --workdir /app node:latest yarn coding-standards-apply
```

### GitHub Actions

We use [GitHub Actions](https://github.com/features/actions) to check coding
standards whenever a pull request is made.

Before making a pull request you can run the GitHub Actions locally to check for
any problems:

[Install `act`](https://github.com/nektos/act#installation) and run

```sh
act -P ubuntu-latest=shivammathur/node:focal pull_request
```

(cf. <https://github.com/shivammathur/setup-php#local-testing-setup>).

### Twigcs

To run only twigcs:

```sh
composer coding-standards-check/twigcs
```

But this is also a part of

```sh
composer coding-standards-check
```

## Build assets

```sh
docker run --volume ${PWD}:/app --workdir /app node:latest yarn install
docker run --volume ${PWD}:/app --workdir /app node:latest yarn encore dev
 ```
