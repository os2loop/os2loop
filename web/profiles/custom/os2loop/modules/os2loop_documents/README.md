# OS2Loop Documents

Documents and document collections.

## Legacy documents

*Legacy document* are documents with a non-empty body field and these exist only
as content migrated from the old OS2Loop system. The body field is only
available on legacy documents.

## Printing to pdf

We use [Entity Print](https://www.drupal.org/project/entity_print) for
printing documents and collections, i.e converting them to PDF.

Entity Print is configured to use
[`dompdf`](https://github.com/dompdf/dompdf) for converting
HTML to PDF.

### Assets in Docker

When using docker we need to help phpfpm locate assets. We use an event
subscriber to alter the pdf and use our custom base url. The base url is defined
in settings.php

```php
$settings['pdf_custom_base_url'] = 'http://nginx:8080/';
```

### Debugging entity print input

See <https://www.drupal.org/node/2706755#debugging> for help on debugging the
templates and resulting HTML that will be used to generate the final PDF.

## Fixtures

```sh
vendor/bin/drush --yes pm:enable os2loop_documents_fixtures
vendor/bin/drush --yes content-fixtures:load --groups=os2loop_documents,os2loop_file,os2loop_taxonomy
vendor/bin/drush --yes pm:uninstall content_fixtures
```
