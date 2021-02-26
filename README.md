# os2loop

os2loop is built in Drupal 9.

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
vendor/bin/drush --yes site:install os2loop --site-name='OS2Loop'
```

Install the modules you need, e.g.:

```sh
vendor/bin/drush pm:enable os2loop_question --yes
```

### Development

See [docs/development](docs/development/README.md) for details on development.

```sh
docker-compose up --detach
docker-compose exec phpfpm composer install
docker-compose exec phpfpm vendor/bin/drush --yes site:install os2loop --site-name='OS2Loop'
# Get the site url
echo "http://$(docker-compose port nginx 80)"
# Get admin sign in url
docker-compose exec phpfpm vendor/bin/drush --yes \
  --uri="http://$(docker-compose port nginx 80)" user:login
```

#### Using `symfony` binary

```sh
docker-compose up --detach
symfony composer install
symfony php vendor/bin/drush --yes site:install os2loop --site-name='OS2Loop'
# Start the server
symfony local:server:start --port=8000 --daemon
# Get the site url
echo "http://127.0.0.1:8000"
# Get admin sign in url
symfony php vendor/bin/drush --uri=https://127.0.0.1:8000 user:login
```

## Updates

```sh
composer install --no-dev --optimize-autoloader
vendor/bin/drush --yes updatedb
vendor/bin/drush --yes config:import
vendor/bin/drush --yes cache:rebuild
```

## Coding standards

```sh
composer check-coding-standards
composer apply-coding-standards
```

```sh
docker run --volume ${PWD}:/app --workdir /app node:latest yarn install
docker run --volume ${PWD}:/app --workdir /app node:latest yarn coding-standards-check
```
