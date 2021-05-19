<?php

namespace Drupal\os2loop_menu_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\os2loop_section_page_fixtures\Fixture\SectionPageFixture;

/**
 * Main menu fixture.
 *
 * @package Drupal\os2loop_menu_fixtures\Fixture
 */
class MainMenuFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    MenuLinkContent::create([
      'title' => 'Ask a question',
      'link' => ['uri' => 'internal:/node/add/os2loop_question'],
      'menu_name' => 'main',
      'expanded' => TRUE,
      'weight' => 0,
    ])->save();

    $page = $this->getReference('os2loop_section_page:The first section page');
    $menuLink = MenuLinkContent::create([
      'title' => 'The first section page',
      'link' => ['uri' => 'internal:/node/' . $page->id()],
      'menu_name' => 'main',
      'expanded' => TRUE,
      'weight' => 1,
    ]);
    $menuLink->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_menu'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      SectionPageFixture::class,
    ];
  }

}
