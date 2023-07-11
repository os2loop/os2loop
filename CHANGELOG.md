# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [feature/update-drupal-and-php]

- Upgrade drupal core (9.5.10)
- Upgrade contrib module
- Change code analysis tool (drupal-check -> phpstan for more configuration options)
- Update custom modules with phpcs and phpstan tools.
- Update configuration to match drupal upgrade.
- Upgrade docker setup to use php 8.1

## [develop]
- [LOOP-862](https://jira.itkdev.dk/browse/LOOP-862): Added documentation for modules and hooks.
- [LOOP-947](https://jira.itkdev.dk/browse/LOOP-947): Styling user profile page
- [LOOP-948](https://jira.itkdev.dk/browse/LOOP-948): Fix position of user dropdown menu, that extends outside the viewport on narrow screens.
- [LOOP-950](https://jira.itkdev.dk/browse/LOOP-950): Styling of messages list
- [LOOP-949](https://jira.itkdev.dk/browse/LOOP-949): Styling of subscriptions page
- [LOOP-732](https://jira.itkdev.dk/browse/LOOP-732),
  [LOOP-733](https://jira.itkdev.dk/browse/LOOP-733) and
  [LOOP-734](https://jira.itkdev.dk/browse/LOOP-734): Drupal, SAML and OpenID
  Connect login
- [LOOP-874](https://jira.itkdev.dk/browse/LOOP-874): Fine-grained
  administrator permissions.
- [LOOP-934](https://jira.itkdev.dk/browse/LOOP-934): Several changes to design
- [LOOP-809](https://jira.itkdev.dk/browse/LOOP-809): Changed search settings, Add tagging to media library, fix bugged file reference in display
- [LOOP-968](https://jira.itkdev.dk/browse/LOOP-968): Remove Connected accounts tab
