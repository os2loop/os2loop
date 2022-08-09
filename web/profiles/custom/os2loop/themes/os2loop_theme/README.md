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
docker run --volume ${PWD}:/app --workdir /app node:16 yarn install
docker run --volume ${PWD}:/app --workdir /app node:16 yarn build
```

Watch for changes:

```sh
docker run --interactive --tty --volume ${PWD}:/app --workdir /app node:latest yarn watch
```

During development you may want to run it with your locally installed
[`yarn`](https://classic.yarnpkg.com/en/docs/install/) binary:

```sh
yarn install
yarn build
```

and maybe even watch for changes:

```sh
yarn watch
```
