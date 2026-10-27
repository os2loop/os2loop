# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.5.0] - 2026-10-29

- [PR-402](https://github.com/itk-dev/os2loop/pull/402)
  - Update drupal core ^11.4
  - Update contrib modules minor and major versions

## [1.4.3] - 2026-10-27

- [PR-406](https://github.com/itk-dev/os2loop/pull/406)
  Updated OpenID Connect config

## [1.4.2] - 2026-10-26

- [PR-404](https://github.com/itk-dev/os2loop/pull/404)
  Switched to maintained `nginx/nginx-unprivileged` docker image

## [1.4.1] - 2026-07-07

- [PR-398](https://github.com/itk-dev/os2loop/pull/398)
  Updated `node` docker compose setup

## [1.4.0] - 2026-06-29

- [PR-396](https://github.com/itk-dev/os2loop/pull/396)
  Security update
- [PR-395](https://github.com/itk-dev/os2loop/pull/395)
  7840: Improved handling of empty step title
- [PR-394](https://github.com/itk-dev/os2loop/pull/394)
  Made `NGINX_FASTCGI_READ_TIMEOUT` have effect

## [1.3.3] - 2026-05-20

- [PR-393](https://github.com/itk-dev/os2loop/pull/393)
  - Drupal core security update

## [1.3.2]

- [PR-392](https://github.com/itk-dev/os2loop/pull/392)
  - Update admin forms to match d11
  - Update OIDC container

- [PR-391](https://github.com/itk-dev/os2loop/pull/391)
  Mail development setup

## [1.3.1]

- [PR-390](https://github.com/itk-dev/os2loop/pull/390)
  Updated php spreadsheet

## [1.3.0]

- [PR-382](https://github.com/itk-dev/os2loop/pull/382)
  Updated drupal core Drupal 11.3.7

## [1.2.4]

- [PR-388](https://github.com/itk-dev/os2loop/pull/388)
  Cleaned up robots.txt
- [PR-387](https://github.com/itk-dev/os2loop/pull/387)
  Disabled caching of error pages
- [PR-385](https://github.com/itk-dev/os2loop/pull/385)
  Cleaned up OpenID Connect settings and removed some obsolete custom settings
- [PR-384](https://github.com/itk-dev/os2loop/pull/384)
  Set access permission on search view
- [PR-383](https://github.com/itk-dev/os2loop/pull/383)
  Fixed OpenID Connect redirect URLs
- [PR-377](https://github.com/itk-dev/os2loop/pull/377)
  - Security update
  - Code cleanup
  - d11 preparation

## [1.2.3]

- [PR-372](https://github.com/itk-dev/os2loop/pull/372)
  Migrate to DRUSH_OPTIONS_URI

## [1.2.2]

- [PR-371](https://github.com/itk-dev/os2loop/pull/371)
  Added GitHub action to build release
  Added auto deployment
- [PR-370](https://github.com/itk-dev/os2loop/pull/370)
  Added woodpecker to test site deployment
- [PR-369](https://github.com/itk-dev/os2loop/pull/369) Update drupal core 10.4.5

## [1.2.1]

- DevOps: Update docker setup to ensure real IPs in logs
- [PR-363](https://github.com/itk-dev/os2loop/pull/367)
  Security update of Drupal core
  Fixed code error in switch cases

## [1.2.0]

- [PR-366](https://github.com/itk-dev/os2loop/pull/366)
  - Security update
    - Drupal Core 10.3.6 => 10.4.3
    - XLS serialization 1.4.0 => 2.0.0

## [1.1.4]

- [PR-363](https://github.com/itk-dev/os2loop/pull/363)
  Security update

## [1.1.3]

- [PR-363](https://github.com/itk-dev/os2loop/pull/363)
  Security update

## [1.1.2]

- [PR-362](https://github.com/itk-dev/os2loop/pull/362)
  - Change Share with a friend form
  - Add chosen lib
  - Use chosen lib for profession and expertise fields
- [PR-361](https://github.com/itk-dev/os2loop/pull/361)
  Added local OIDC server-mock

## [1.1.1]

- [PR-360](https://github.com/itk-dev/os2loop/pull/360)
  Fix bug in toc

## [1.1.0]

- Update config to mach drupal version.
- [PR-358](https://github.com/itk-dev/os2loop/pull/358)
  Update pdf css to ensure display of all paragraphs

## [1.0.0]

- Upgrade drupal core (10.3.1)
- Switch to dompdf
- Upgrade drupal core (9.5.10)
- Upgrade contrib module
- Change code analysis tool
  drupal-check -> phpstan for more configuration options
- Update custom modules with phpcs and phpstan tools.
- Update configuration to match drupal upgrade.
- Upgrade docker setup to use php 8.1
- [PR-354](https://github.com/itk-dev/os2loop/pull/354)
  1712: Added “Expand all steps” link on step by step
- [PR-353](https://github.com/itk-dev/os2loop/pull/353):
  Security update
- [PR-352](https://github.com/itk-dev/os2loop/pull/352):
  Fix spacing
- [PR-351](https://github.com/itk-dev/os2loop/pull/351):
  Updated tasks
- [PR-341](https://github.com/itk-dev/os2loop/pull/341):
  Notify of collection changed when document is changed.
- [LOOP-862](https://jira.itkdev.dk/browse/LOOP-862): Added documentation for
modules and hooks.
- [LOOP-947](https://jira.itkdev.dk/browse/LOOP-947): Styling user profile page
- [LOOP-948](https://jira.itkdev.dk/browse/LOOP-948): Fix position of user
dropdown menu, that extends outside the viewport on narrow screens.
- [LOOP-950](https://jira.itkdev.dk/browse/LOOP-950): Styling of messages list
- [LOOP-949](https://jira.itkdev.dk/browse/LOOP-949): Styling of subscriptions
page
- [LOOP-732](https://jira.itkdev.dk/browse/LOOP-732),
  [LOOP-733](https://jira.itkdev.dk/browse/LOOP-733) and
  [LOOP-734](https://jira.itkdev.dk/browse/LOOP-734): Drupal, SAML and OpenID
  Connect login
- [LOOP-874](https://jira.itkdev.dk/browse/LOOP-874): Fine-grained
  administrator permissions.
- [LOOP-934](https://jira.itkdev.dk/browse/LOOP-934): Several changes to design
- [LOOP-809](https://jira.itkdev.dk/browse/LOOP-809): Changed search settings,
Add tagging to media library, fix bugged file reference in display
- [LOOP-968](https://jira.itkdev.dk/browse/LOOP-968): Remove Connected accounts
tab
- [LOOP-2056](https://leantime.itkdev.dk/dashboard/home#/tickets/showTicket/2056):
Create new frontpage with fixtures.

[Unreleased]: https://github.com/itk-dev/os2loop/compare/1.5.0...HEAD
[1.5.0]: https://github.com/itk-dev/os2loop/compare/1.4.3...1.5.0
[1.4.3]: https://github.com/itk-dev/os2loop/compare/1.4.2...1.4.3
[1.4.2]: https://github.com/itk-dev/os2loop/compare/1.4.1...1.4.2
[1.4.1]: https://github.com/itk-dev/os2loop/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/itk-dev/os2loop/compare/1.3.3...1.4.0
[1.3.3]: https://github.com/itk-dev/os2loop/compare/1.3.1..1.3.2...1.3.3
[1.3.2]: https://github.com/itk-dev/os2loop/compare/1.3.1..1.3.2
[1.3.1]: https://github.com/itk-dev/os2loop/compare/1.3.0..1.3.1
[1.3.0]: https://github.com/itk-dev/os2loop/compare/1.2.4..1.3.0
[1.2.4]: https://github.com/itk-dev/os2loop/compare/1.2.3..1.2.4
[1.2.3]: https://github.com/itk-dev/os2loop/compare/1.2.2..1.2.3
[1.2.2]: https://github.com/itk-dev/os2loop/compare/1.2.1..1.2.2
[1.2.1]: https://github.com/itk-dev/os2loop/compare/1.2.0..1.2.1
[1.2.0]: https://github.com/itk-dev/os2loop/compare/1.1.4..1.2.0
[1.1.4]: https://github.com/itk-dev/os2loop/compare/1.1.3..1.1.4
[1.1.3]: https://github.com/itk-dev/os2loop/compare/1.1.2..1.1.3
[1.1.2]: https://github.com/itk-dev/os2loop/compare/1.1.1..1.1.2
[1.1.1]: https://github.com/itk-dev/os2loop/compare/1.1.0..1.1.1
[1.1.0]: https://github.com/itk-dev/os2loop/compare/1.0.0..1.1.0
[1.0.0]: https://github.com/itk-dev/os2loop/releases/tag/1.0.0
