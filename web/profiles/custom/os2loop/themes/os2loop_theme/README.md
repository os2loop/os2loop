# OS2Loop theme

This is the default theme for OS2Loop.

## File structure

- Follows the template structure of stable9 theme.
- Js and scss is located in assets folder and compiled into build folder.

## Building assets

Assets are built using [Symfony
Encore](https://symfony.com/doc/current/frontend/encore/installation.html#installing-encore-in-non-symfony-applications).

Build assets (JavaScript and CSS) by running

```sh
docker compose run --rm node yarn --cwd web/profiles/custom/os2loop/themes/os2loop_theme install
docker compose run --rm node yarn --cwd web/profiles/custom/os2loop/themes/os2loop_theme build
```

Watch for changes:

```sh
docker compose run --rm node yarn --cwd web/profiles/custom/os2loop/themes/os2loop_theme watch
```
