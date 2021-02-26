# OS2Loop development

## Content types

Each content type

* has a machine name prefixed with `os2loop_`, i.e. the machine name of the Page
  content type is `os2loop_page`
* has its own text formats for any formatted fields on the content type.
* has its own module for storing config related to the content type (fields,
  text formats, …) and all config machine names must be prefixed with the module
  name, e.g `os2loop_page_content` as machine name for the Content field on the
  Page content type. See [Shared fields](#shared-fields) below for exceptions to
  this rule.

### Fields

The machine name of a field on a content type is prefixed with the machine name
of the content type, i.e. the machine name of the Content field on the Page
content type is `os2loop_page_content`.

Note that field machine names do not start with `field_`.

When installing the Field UI module to edit fields on a content type,
`field_ui.settings` should be set to the empty string:

```sh
vendor/bin/drush pm:enable field_ui
vendor/bin/drush --yes config:set field_ui.settings field_prefix -- ''
```

After editing content type fields the Field UI module **must be uninstalled**
before updated content type configuration is exported.

### Shared fields

A number of fields must be shared between content types in Loop: A field for
each of the three taxonomies (`os2loop_shared_subject`, `os2loop_shared_tags`
and `os2loop_shared_profession`) and one for revision date
(`os2loop_shared_revison_data`).

## Configuration

As mentioned above all configuration for a content type must be stored in the
content type’s module (in the `config/install` folder). The configuration must
be installed when installing the module and must be removed when uninstalling
the module. To make this work, all configuration (files) for a module must have
both a dependency and an “enforced dependency” on the module itself (cf.
<https://www.drupal.org/node/2087879>).

The automate adding these dependencies, we have a module, `os2loop_config`, with
a Drush command that can add module dependencies and another command that can
move config into the `config/install` folder inside the module. The commands
uses the fact that all names of configuration related to a content type contains
the machine name of the content type itself.

Note: This command somewhat is similar to the
[`config:export:content:type`](https://drupalconsole.com/docs/en/commands/config-export-content-type)
command the [Drupal Console](https://drupalconsole.com/), but that command does
not add the dependencies needed for our requirements.

Here is an example on how to add module dependencies on the configuration of the
`os2loop_page` content type:

```sh
# Enable the OS2Loop config module.
vendor/bin/drush --yes pm:enable os2loop_config
# Make changes to the content type and related stuff.
# Add module dependencies and remove uuid from the config files (cf. https://www.drupal.org/node/2087879).
vendor/bin/drush os2loop:config:add-module-config-dependencies --remove-uuid os2loop_page
vendor/bin/drush config:export
# Move the config into the module’s config/install folder.
vendor/bin/drush os2loop:config:move-module-config os2loop_page
# Disable the OS2Loop config module.
vendor/bin/drush --yes pm:uninstall os2loop_config
```

**Note**: The `os2loop_shared` module should always be handled last when batch
procession the configuration (@todo explain why), e.g.

```sh
for module in $(ls web/profiles/custom/os2loop/modules/os2loop_*/os2loop_*.info.yml | xargs basename -s .info.yml | grep -v os2loop_shared) os2loop_shared; do
  vendor/bin/drush os2loop:config:add-module-config-dependencies --remove-uuid $module
  vendor/bin/drush os2loop:config:move-module-config $module
done
```

To process all OS2Loop modules in one go use:

```sh
vendor/bin/drush --yes config:export
vendor/bin/drush --yes pm:enable os2loop_config
for module in $(ls web/profiles/custom/os2loop/modules/os2loop_*/os2loop_*.info.yml | xargs basename -s .info.yml | grep -v os2loop_shared) os2loop_shared; do
  vendor/bin/drush os2loop:config:add-module-config-dependencies --remove-uuid $module
done
for module in $(ls web/profiles/custom/os2loop/modules/os2loop_*/os2loop_*.info.yml | xargs basename -s .info.yml | grep -v os2loop_shared) os2loop_shared; do
  vendor/bin/drush os2loop:config:move-module-config $module
done
vendor/bin/drush --yes pm:uninstall os2loop_config
```

## Sub-modules

Each OS2Loop content type module must have two sub-modules: One for loading
fixtures and one for testing the content type. See [Testing](Testing.md).

Continuing the `os2loop_page` example, these modules must be placed in the
`modules` folder under the `os2loop_page` module folder:

```sh
web/profiles/custom/os2loop/modules/os2loop_page
├── modules
│   ├── os2loop_page_fixtures
│   ├── os2loop_page_tests
│   └── os2loop_page_tests_cypress
```
