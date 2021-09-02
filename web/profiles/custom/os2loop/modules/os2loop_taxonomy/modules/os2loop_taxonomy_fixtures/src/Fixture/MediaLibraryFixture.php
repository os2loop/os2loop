<?php

namespace Drupal\os2loop_taxonomy_fixtures\Fixture;

/**
 * Subject fixture.
 *
 * @package Drupal\os2loop_taxonomy_fixtures\Fixture
 */
class MediaLibraryFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'os2loop_media_library';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Billede' => [
      'Billede til dokument',
      'Billede til indlæg',
      'Billede til spørgsmål',
    ],

    'Fil' => [
      'Godkendt af leder',
      'Vejledning',
      'Testet for tilgængelighed',
    ],
  ];

}
