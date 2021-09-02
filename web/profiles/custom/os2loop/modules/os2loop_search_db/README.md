# OS2Loop Search DB

Two exposed forms are available as blocks: One with just the search field and
one with the search field plus sorting of results.

The former is shown on all node types that are indexed and the latter is shown
only on the search page itself.

## Comments in search

Comments, i.e. answers on questions and comments on posts, are included in the
search index and a facet for filtering on comment type is set up. However, we
don't expose this facet to the user. Instead we use it to include comments on
content when filtering on content type, i.e. when filtering on “Question” we
also include “Answer” (a comment type) in the search query (see
`Drupal\os2loop_search_db\Helper\Helper::alterSearchApiQuery` for implementation
details).

## Grouped content types

When applying facet filters content types can be grouped, e.g. filtering on
the content type “Document” can also includes “Document collection” and “External
content” and this is the default grouping.

The “content type groups” can be overwritten `settings.local.php` and the
default grouping mentioned above can be defined defined as:

```sh
$config['os2loop.settings']['os2loop_search_db']['content_type_groups'] = [
  'os2loop_documents_document' => [
    'os2loop_documents_collection',
    'os2loop_external',
  ],
];
```

If “Document” should only include “Document collection”, use

```sh
$config['os2loop.settings']['os2loop_search_db']['content_type_groups'] = [
  'os2loop_documents_document' => [
    'os2loop_documents_collection',
  ],
];
```

Use

```sh
$config['os2loop.settings']['os2loop_search_db']['content_type_groups'] = [];
```

to disable the content type grouping.

## Indexing content

Content is indexed immediately when its created or updated.

If you need to (re-)index content manually, run

```sh
vendor/bin/drush search-api:reset-tracker os2loop_search_db_index
vendor/bin/drush search-api:rebuild-tracker os2loop_search_db_index
vendor/bin/drush search-api:index os2loop_search_db_index
```
