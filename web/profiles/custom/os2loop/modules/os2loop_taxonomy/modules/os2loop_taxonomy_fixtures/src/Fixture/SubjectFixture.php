<?php

namespace Drupal\os2loop_taxonomy_fixtures\Fixture;

/**
 * Subject fixture.
 *
 * @package Drupal\os2loop_taxonomy_fixtures\Fixture
 */
class SubjectFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'os2loop_taxonomy_subject';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Diverse',
    'Dokumentation',
    'Forebyggende',
    'Forløb & Genoptræning',
    'Funktionsvurdering',
    'Kommunikation',
    'Magtanvendelse',
    'Medicin',
    'Mobil (HH2 + HH3)',
    'Opgradering',
    'Opsætning af system (brugerprofil / indstillinger)',
    'Overblik',
    'Planlægning (Booking, Disponering)',
    'Sagsbehandling',
    'Stamdata / oplysninger',
  ];

}
