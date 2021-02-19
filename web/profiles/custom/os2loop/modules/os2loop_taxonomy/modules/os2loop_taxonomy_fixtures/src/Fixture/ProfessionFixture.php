<?php

namespace Drupal\os2loop_taxonomy_fixtures\Fixture;

/**
 * Profession fixture.
 *
 * @package Drupal\os2loop_taxonomy_fixtures\Fixture
 */
class ProfessionFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'os2loop_taxonomy_profession';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Administrativ medarbejder',
    'Andet',
    'Borgerkonsulent',
    'Diætist',
    'Elev/studerende',
    'Ergoterapeut',
    'Frivilligkoordinator',
    'Fysioterapeut',
    'Husassistent',
    'IT-koordinator',
    'Konsulent',
    'Leder',
    'Social- og sundhedsassistent',
    'Social- og sundhedshjælper',
    'Sundhedskonsulent',
    'Sygehjælper',
    'Sygeplejerske',
    'Teamleder',
    'Teknisk servicemedarbejder/pedel/håndværker',
    'Visitator/Borgerkonsulent',
    'Økonomaer/køkkenassistent/Ernæringsassistent',
  ];

}
