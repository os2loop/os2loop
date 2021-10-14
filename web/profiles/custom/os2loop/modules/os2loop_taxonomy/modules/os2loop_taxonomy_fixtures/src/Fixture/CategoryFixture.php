<?php

namespace Drupal\os2loop_taxonomy_fixtures\Fixture;

/**
 * Subject fixture.
 *
 * @package Drupal\os2loop_taxonomy_fixtures\Fixture
 */
class CategoryFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'os2loop_category';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Retningslinje',
    'Vejledning',
    'Instruks',
  ];

}
