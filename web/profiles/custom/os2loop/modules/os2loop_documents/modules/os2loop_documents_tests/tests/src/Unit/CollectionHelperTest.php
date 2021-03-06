<?php

namespace Drupal\Tests\os2loop_documents\Unit;

use Drupal\Tests\UnitTestCase;

/**
 *
 */
class CollectionHelperTest extends UnitTestCase {

  /**
   *
   */
  public function testBuildTree() {
    /** @var \Drupal\os2loop_documents\Helper\CollectionHelper $helper */
    $helper = \Drupal::service('os2loop_collection_helper');
    $expected = [];
    $items = [];
    $actual = $helper->buildTree($items);
    $this->assertEquals($expected, $actual);
  }

}
